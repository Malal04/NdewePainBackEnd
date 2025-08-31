<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\auth\AuthRequest;
use App\Http\Requests\auth\ChangePasswordRequest;
use App\Http\Requests\auth\ForgotPasswordRequest;
use App\Http\Requests\auth\RegisterRequest;
use App\Http\Requests\auth\ResetPasswordRequest;
use App\Services\auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Inscription
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        return $this->authService->register($request);
    }

    /**
     * Connexion
     */
    public function login(AuthRequest $request): JsonResponse
    {
        return $this->authService->login($request);
    }

    /**
     * Déconnexion
     */
    public function logout(): JsonResponse
    {
        return $this->authService->logout();
    }

    /**
     * Rafraîchir le token
     */
    public function refreshToken(): JsonResponse
    {
        return $this->authService->refreshToken();
    }

    /**
     * Récupérer l'utilisateur connecté
     */
    public function me(): JsonResponse
    {
        return $this->authService->getUsersMe();
    }

    public function updateProfile($request)
    {
        return $this->authService->updateProfile($request);
    }

    /**
     * Changer le mot de passe
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        return $this->authService->changePassword($request);
    }

    /**
     * Demander la réinitialisation du mot de passe
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        return $this->authService->forgotPassword($request);
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        return $this->authService->resetPassword($request);
    }

    /**
     * Lister tous les utilisateurs (admin, gérant, employé) ou uniquement soi-même (client)
     */
    public function listUsers($request)
    {
        return $this->authService->listUsers($request);
    }

    /**
     * Afficher un seul utilisateur
     */
    public function showUser($id)
    {
        return $this->authService->showUser($id);
    }

    public function changeAccountState( Request $request, $id, AuthService $authService)
    {
        return $authService->changeAccountState($id, $request);
    }


}
