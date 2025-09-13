<?php

namespace App\Http\Requests\stock;

use Illuminate\Foundation\Http\FormRequest;

class PromotionRequest extends FormRequest
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
            'nom' => 'required|string|max:190',
            'description' => 'nullable|string',
            'type_remise' => 'required|in:pourcentage,montant,bogo,gratuit_livraison',
            'valeur_remise' => 'nullable|numeric|min:0',
            'code_promo' => 'nullable|string|max:50|unique:promotions,code_promo',
            'conditions' => 'nullable|string',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'recurrence' => 'nullable|string|max:190',
            'status' => 'nullable|in:active,inactive',
            'produit_ids' => 'array',
            'produit_ids.*' => 'integer|exists:produits,id',
        ];
    }

    public function messages()
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.max' => 'Le nom doit contenir au maximum 190 caractères.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'type_remise.required' => 'Le type de remise est obligatoire.',
            'type_remise.in' => 'Le type de remise doit être soit "pourcentage", soit "montant", soit "bogo", soit "gratuit_livraison".',
            'valeur_remise.numeric' => 'La valeur de la remise doit être un nombre.',
            'valeur_remise.min' => 'La valeur de la remise doit être au moins 0.',
            'code_promo.string' => 'Le code promo doit être une chaîne de caractères.',
            'code_promo.max' => 'Le code promo doit contenir au maximum 50 caractères.',
            'code_promo.unique' => 'Le code promo doit être unique.',
            'conditions.string' => 'Les conditions doivent être une chaîne de caractères.',
            'date_debut.date' => 'La date de début doit être une date valide.',
            'date_fin.date' => 'La date de fin doit être une date valide.',
            'date_fin.after_or_equal' => 'La date de fin doit être après ou égale à la date de début.',
            'recurrence.string' => 'La récurrence doit être une chaîne de caractères.',
            'recurrence.max' => 'La récurrence doit contenir au maximum 190 caractères.',
            'status.in' => 'Le statut doit être soit "active", soit "inactive".',
            'produit_ids.array' => 'Les produits doivent être un tableau.',
            'produit_ids.*.integer' => 'Les produits doivent être des entiers.',
            'produit_ids.*.exists' => 'Les produits doivent exister.',
        ];
    }

}
