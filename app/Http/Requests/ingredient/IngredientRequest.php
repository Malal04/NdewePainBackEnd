<?php

namespace App\Http\Requests\ingredient;

use Illuminate\Foundation\Http\FormRequest;

class IngredientRequest extends FormRequest
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
            'nom'           => 'required|string|max:255',
            'quantite'      => 'required|numeric|min:0',
            'unite'         => 'required|string|max:50',
            'seuil_reappro' => 'required|numeric|min:0',
            'supplier_id'   => 'required|exists:suppliers,id',
            'statut'        => 'in:ok,low,out',
        ];
    }

    public function messages(): array 
    {
        return [
            'nom.required'           => 'Le nom est obligatoire.',
            'quantite.required'      => 'La quantité est obligatoire.',
            'unite.required'         => 'L\'unité est obligatoire.',
            'seuil_reappro.required' => 'Le seuil de reapprovisionnement est obligatoire.',
            'supplier_id.required'   => 'Le fournisseur est obligatoire.',
            'supplier_id.exists'     => 'Le fournisseur spécifié n\'existe pas.',
            'statut.required'        => 'Le statut est obligatoire.',
            'statut.in'              => 'Le statut doit être "ok", "low" ou "out".',
        ];
    }
    
}
