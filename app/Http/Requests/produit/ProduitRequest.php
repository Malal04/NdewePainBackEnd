<?php

namespace App\Http\Requests\produit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProduitRequest extends FormRequest
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
            'categorie_id' => 'required|exists:categories,id',
            'nom'          => 'required|string|max:255',
            'description'  => 'nullable|string',
            'prix_unitaire'=> 'required|numeric|min:0',
            'photo_url'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'stock'        => 'nullable|integer|min:0',
            'status'       => 'in:active,inactive',
            'allergenes'   => 'nullable|array',
            'allergenes.*' => 'string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'categorie_id.required' => 'La catégorie est obligatoire.',
            'categorie_id.exists'   => 'La catégorie n\'existe pas.',
            'nom.required'          => 'Le nom est obligatoire.',

            'prix_unitaire.required'=> 'Le prix unitaire est obligatoire.',
            'prix_unitaire.min'     => 'Le prix unitaire doit être supérieur ou égal à 0.',

            'stock.required'        => 'Le stock est obligatoire.',
            'stock.min'             => 'Le stock doit être supérieur ou égal à 0.',

            'status.required'       => 'Le statut est obligatoire.',
            'allergenes.required'   => 'Les allergènes sont obligatoires.',
            'allergenes.array'      => 'Les allergènes doivent être un tableau.',
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
