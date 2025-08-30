<?php

namespace App\Http\Requests\auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AuthRequest extends FormRequest
{
    
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email'    => 'required|email|max:255',
            'password' => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'   => 'L\'email est obligatoire.',
            'email.email'      => 'Le format de l\'email est invalide.',
            'email.max'        => 'L\'email ne doit pas dépasser 255 caractères.',
            
            'password.required'=> 'Le mot de passe est obligatoire.',
            'password.string'  => 'Le mot de passe doit être une chaîne de caractères.',
            'password.min'     => 'Le mot de passe doit contenir au moins 6 caractères.',
            'password.max'     => 'Le mot de passe ne doit pas dépasser 255 caractères.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => 'Erreur de validation. Veuillez vérifier les données saisies.',
            'errors' => $validator->errors(),
        ], 422));
    }

}
