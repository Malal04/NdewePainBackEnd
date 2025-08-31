<?php

namespace App\Http\Requests\adresse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AdresseRequest extends FormRequest
{
    
    /**
     * Déterminer si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation pour la requête.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ligne_adresse'  => 'required|string|max:255',
            'ville'          => 'required|string|max:100',
            'code_postal'    => 'nullable|string|max:20',
            'pays'           => 'nullable|string|max:100',
            'est_principale' => 'boolean',
            'type'           => 'required|in:maison,bureau,boulangerie,autre',
            'mode_livraison' => 'required|in:livraison,retrait',
        ];
    }

    /**
     * Messages personnalisés (optionnel).
     */
    public function messages(): array
    {
        return [
            'ligne_adresse.required'  => 'L’adresse est obligatoire.',
            'ville.required'          => 'La ville est obligatoire.',
            'type.in'                 => 'Le type doit être maison, bureau ou autre.',
            'est_principale.boolean'  => 'Le champ est_principale doit être un booléen.',
            'code_postal.max'         => 'Le code postal ne peut pas avoir plus de 20 caractères.',
            'pays.max'                => 'Le pays ne peut pas avoir plus de 100 caractères.',
            'ligne_adresse.max'       => 'L’adresse ne peut pas avoir plus de 255 caractères.',
            'ville.max'               => 'La ville ne peut pas avoir plus de 100 caractères.',
            'type.required'           => 'Le type est obligatoire.',
            'mode_livraison.in'       => 'Le mode de livraison doit être livraison ou retrait.',
            'mode_livraison.required' => 'Le mode de livraison est obligatoire.',
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
