<?php

namespace App\Http\Requests\stock;

use Illuminate\Foundation\Http\FormRequest;

class StockMoveRequest extends FormRequest
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
            'produit_id'    => 'required|integer|exists:produits,id',
            'type_mouvement'=> 'required|in:entree,sortie,ajustement',
            'quantite'      => 'required|integer|min:1',
            'commentaire'   => 'nullable|string|max:500',
            'seuil_minimum' => 'sometimes|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'type_mouvement.required' => 'Le type de mouvement est obligatoire.',
            'type_mouvement.in'       => 'Le type de mouvement doit être "entree", "sortie" ou "ajustement".',
            'quantite.required'       => 'La quantité est obligatoire.',
            'quantite.min'            => 'La quantité doit être au moins 1.',
        ];
    }
}
