<?php

namespace App\Http\Requests\auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    /**
     * Déterminer si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation appliquées à la requête.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nom'       => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'telephone' => 'required|string|max:20|unique:users,phone',
            'profil'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'password'  => 'required|string|min:6|confirmed',
            'role'      => 'required|in:client,admin,gerant,employe',
        ];
    }

    /**
     * Messages personnalisés pour les erreurs de validation.
     */
    public function messages(): array
    {
        return [
            'nom.required'        => 'Le nom est obligatoire.',
            'nom.string'          => 'Le nom doit être une chaîne de caractères.',
            'nom.max'             => 'Le nom ne doit pas dépasser 255 caractères.',

            'email.required'      => 'L\'email est obligatoire.',
            'email.email'         => 'Le format de l\'email est invalide.',
            'email.max'           => 'L\'email ne doit pas dépasser 255 caractères.',
            'email.unique'        => 'Cet email est déjà utilisé.',

            'telephone.required'    => 'Le numéro de téléphone est obligatoire.',
            'telephone.max'       => 'Le numéro de téléphone ne doit pas dépasser 20 caractères.',
            'telephone.unique'    => 'Ce numéro de téléphone est déjà utilisé.',

            'profil.image'        => 'Le fichier doit être une image.',
            'profil.mimes'        => 'L\'image doit être au format JPG, JPEG ou PNG.',
            'profil.max'          => 'La taille de l\'image ne doit pas dépasser 2 Mo.',

            'password.required'   => 'Le mot de passe est obligatoire.',
            'password.string'     => 'Le mot de passe doit être une chaîne de caractères.',
            'password.min'        => 'Le mot de passe doit contenir au moins 6 caractères.',
            'password.confirmed'  => 'La confirmation du mot de passe ne correspond pas.',
            
            'role.required'       => 'Le rôle est obligatoire.',
            'role.in'             => 'Le rôle doit être "client" ou "admin" ou "gerant" ou "employe".',
            
        ];
    }

    /**
     * Réponse JSON personnalisée en cas d’échec de validation.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => false,
            'message' => 'Erreur de validation. Veuillez vérifier les données saisies.',
            'errors'  => $validator->errors(),
        ], 422));
    }

}
