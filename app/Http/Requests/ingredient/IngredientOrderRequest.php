<?php

namespace App\Http\Requests\ingredient;

use Illuminate\Foundation\Http\FormRequest;

class IngredientOrderRequest extends FormRequest
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
            'ingredient_id' => 'required|exists:ingredients,id',
            'supplier_id'   => 'required|exists:suppliers,id',
            'quantite'      => 'required|numeric|min:0.1',
            'statut'        => 'in:en_attente,livrer,annuler',
        ];
    }

    public function messages(): array
    {
        return [
            'ingredient_id.required' => 'L\'ingrédient est obligatoire.',
            'ingredient_id.exists'   => 'L\'ingrédient spécifié n\'existe pas.',
            'supplier_id.required'   => 'Le fournisseur est obligatoire.',
            'supplier_id.exists'     => 'Le fournisseur spécifié n\'existe pas.',
            'quantite.required'      => 'La quantité est obligatoire.',
            'quantite.numeric'       => 'La quantité doit être un nombre.',
            'quantite.min'           => 'La quantité doit être au moins 0.1.',
            'statut.in'              => 'Le statut doit être en_attente, livrer ou annuler.',
        ];
    }
}
