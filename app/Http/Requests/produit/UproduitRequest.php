<?php

namespace App\Http\Requests\produit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class UproduitRequest extends FormRequest
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
            'categorie_id' => 'required|integer',
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prix_unitaire' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'photo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'allergenes' => 'nullable|array',
            'allergenes.*' => 'string|max:255',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return response()->json([
            'status' => false,
            'message' => 'Erreur lors de la mise à jour du produit.',
            'error' => $validator->errors()
        ], 422);
    }
    
    
}
