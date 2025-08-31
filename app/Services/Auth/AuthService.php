<?php

namespace App\Services\auth;

use App\Http\Requests\auth\AuthRequest;
use App\Http\Requests\auth\ChangePasswordRequest;
use App\Http\Requests\auth\ForgotPasswordRequest;
use App\Http\Requests\auth\RegisterRequest;
use App\Http\Requests\auth\ResetPasswordRequest;
use App\Http\Resources\auth\AuthResource;
use App\Http\Resources\auth\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;  


class AuthService
{

    public function register(RegisterRequest $request)
    {

        $profilePath = null;
        if ($request->hasFile('profil')) {
            $profilePath = $request->file('profil')->store('profiles', 'public');
        }

        $user = User::create([
            'nom'          => $request->nom,
            'email'        => $request->email,
            'phone'        => $request->telephone,
            'profile'      => $profilePath ?? null ,
            'password'     => Hash::make($request->password),
            'role'         => $request->role,
            'accountState' => 'actived',
        ]);

        return response()->json([
            'status'  => true,
            'user'    => new AuthResource($user),
            'role'    => $user->role,
            'accountState' => $user->accountState,
            'message' => 'Utilisateur créé avec succès.',
        ], 201);
    }

    public function login(AuthRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        // Vérification des identifiants
        if (!$user || !Hash::check(
            $request->password, 
            $user->password
        )) {
            throw ValidationException::withMessages([
                'message' => ['Email ou mot de passe incorrect.'],
            ]);
        }

        // Vérification de l'état du compte
        if ($user->accountState === 'blocked') {
            throw ValidationException::withMessages([
                'message' => ['Votre compte est bloqué.'],
            ]);
        }

        $token = JWTAuth::attempt([
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if (!$token) {
            throw ValidationException::withMessages([
                'message' => ['Échec de l\'authentification.'],
            ]);
        }

        return response()->json([
            'status'      => true,
            'token'       => $token,
            'user'        => new AuthResource($user),
            'accountState' => $user->accountState,
            'role'        => $user->role,
            'message'     => 'Utilisateur connecté avec succès.',
        ]);
    }

    public function logout()
    {
        JWTAuth::logout();

        return response()->json([
            'status'  => true,
            'message' => 'Déconnexion réussie.',
        ]);
    }

    public function refreshToken()
    {
        $token = JWTAuth::refresh();

        return response()->json([
            'status'      => true,
            'message'     => 'Token rafraîchi avec succès.',
            'token'       => $token,
        ]);
    }

    public function getUsersMe()
    {
        $user = Auth::user();

        return response()->json([
            'status' => true,
            'user'   => new UserResource($user),
        ]);
    }

    public function updateProfile($request)
    {
        $user = User::find(Auth::id());

        if (!$user) {
            throw ValidationException::withMessages([
                'message' => ['Utilisateur introuvable. Veuillez vous reconnecter.'],
            ]);
        }

        $updateData = [];

        if ($request->filled('nom')) {
            $updateData['nom'] = $request->nom;
        }

        if ($request->filled('email') && $request->email !== $user->email) {
            $updateData['email'] = $request->email;
        }

        if ($request->filled('phone') && $request->phone !== $user->phone) {
            $updateData['phone'] = $request->phone;
        }

        // Gestion du profil (upload si nouvelle image)
        if ($request->hasFile('profil')) {
            $profilePath = $request->file('profil')->store('profiles', 'public');
            $updateData['profile'] = $profilePath;
        }

        // Mise à jour uniquement si des données ont changé
        if (!empty($updateData)) {
            $user->update($updateData);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Profil mis à jour avec succès.',
            'user'    => new UserResource($user),
        ]);
    }

    public function changePassword( ChangePasswordRequest $request)
    {
        $user = User::find(Auth::id());

        if (!$user) {
            throw ValidationException::withMessages([
                'message' => ['Utilisateur introuvable. Veuillez vous reconnecter.'],
            ]);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'message' => ['Le mot de passe actuel est incorrect. Veuillez vous reconnecter.'],
            ]);
        }
        
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);
        
        return response()->json([
            'status'  => true,
            'message' => 'Mot de passe modifié avec succès.',
        ]);
    }

    public function forgotPassword( ForgotPasswordRequest $request)
    {
        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json([
                'status'  => false,
                'message' => 'Impossible d\'envoyer le lien de réinitialisation.',
            ], 400);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Lien de réinitialisation envoyé à votre email.',
        ]);
    }
   
    public function resetPassword( ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'status'  => false,
                'message' => 'Échec de la réinitialisation du mot de passe.',
            ], 400);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Mot de passe réinitialisé avec succès.',
        ]);
    }

    public function listUsers($request)
    {
        $user = Auth::user();

        // Si client → ne peut voir que lui-même
        if ($user->role === 'client') {
            return response()->json([
                'status' => true,
                'users'  => [new UserResource($user)],
            ]);
        }

        // Admin / Gérant / Employé → accès à tous les utilisateurs
        $query = User::query();

        // Ajout d'un filtre optionnel (ex: ?role=client ou ?nom=John)
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }
        if ($request->has('nom')) {
            $query->where('nom', 'like', '%' . $request->nom . '%');
        }
        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        // Pagination (par défaut 10 par page)
        $perPage = $request->get('per_page', 10);
        $users = $query->latest()->paginate($perPage);

        return response()->json([
            'status' => true,
            'users'  => UserResource::collection($users),
            'meta'   => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
            ],
        ]);
    }

    public function showUser($id)
    {
        $authUser = Auth::user();

        // Si c’est un client → il ne peut voir que lui-même
        if ($authUser->role === 'client' && $authUser->id != $id) {
            return response()->json([
                'status'  => false,
                'message' => 'Accès refusé.',
            ], 403);
        }

        // On récupère l'utilisateur demandé
        $user = User::with('addresses')->find($id);

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Utilisateur introuvable.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'user'   => new UserResource($user),
        ]);
    }

    public function changeAccountState($id, $request)
    {
        $authUser = Auth::user();

        // Seul un admin ou gérant peut changer l'état d'un compte
        if (!in_array($authUser->role, ['admin', 'Gerant'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Accès refusé. Vous n\'avez pas les permissions nécessaires.',
            ], 403);
        }

        // Récupération de l'utilisateur ciblé
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Utilisateur introuvable.',
            ], 404);
        }

        // Vérifie que la valeur envoyée est valide
        if (!in_array($request->accountState, ['actived', 'blocked'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Valeur invalide. Les états possibles sont: actived, blocked.',
            ], 400);
        }

        // Mise à jour de l'état
        $user->update([
            'accountState' => $request->accountState
        ]);

        return response()->json([
            'status'  => true,
            'message' => "L'état du compte a été mis à jour avec succès.",
            'user'    => new UserResource($user),
        ]);
    }


}
