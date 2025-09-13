<?php

namespace App\Http\Requests\stock;

use Illuminate\Foundation\Http\FormRequest;

class StockRequest extends FormRequest
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
            'quantite_actuelle' => 'sometimes|integer|min:0',
            'seuil_minimum' => 'sometimes|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'produit_id.required' => 'Le produit est obligatoire.',
            'produit_id.exists'   => 'Le produit doit exister.',
            'quantite_actuelle.integer' => 'La quantité doit être un entier.',
            'seuil_minimum.min' => 'Le seuil minimum doit être au moins 0.',
        ];
    }
    
}
