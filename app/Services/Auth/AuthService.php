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

    /**
     * Inscription d'un nouvel utilisateur
     */
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

    /**
     * Connexion de l'utilisateur
     */
    public function login(AuthRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        // Vérification des identifiants
        if (!$user || !Hash::check($request->password, $user->password)) {
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

        // Tentative d'authentification
        $token = JWTAuth::attempt([
            'email'    => $request->email,
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

    /**
     * Déconnexion de l'utilisateur
     */
    public function logout()
    {
        JWTAuth::logout();

        return response()->json([
            'status'  => true,
            'message' => 'Déconnexion réussie.',
        ]);
    }

    /**
     * Rafraîchir le token JWT
     */
    public function refreshToken()
    {
        $token = JWTAuth::refresh();

        return response()->json([
            'status'      => true,
            'message'     => 'Token rafraîchi avec succès.',
            'token'       => $token,
        ]);
    }

    /**
     * Récupérer l'utilisateur connecté
     */
    public function getUsersMe()
    {
        $user = Auth::user();

        return response()->json([
            'status' => true,
            'user'   => new UserResource($user),
        ]);
    }

    /**
     * Changer le mot de passe
     */
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

    /**
     * Demande de réinitialisation du mot de passe (forgot password)
     */
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

    /**
     * Réinitialisation du mot de passe avec token
     */
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

}
