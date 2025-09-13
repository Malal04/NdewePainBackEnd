<?php

namespace App\Http\Requests\produit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CategorieRequest extends FormRequest
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
            'nom'        => 'required|string|max:255|unique:categories,nom,' . $this->route('categorie'),
            'slug'       => 'nullable|string|unique:categories,slug,' . $this->route('categorie'),
            'description'=> 'nullable|string',
            'status'     => 'in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de la catégorie est obligatoire.',
            'nom.unique'   => 'Ce nom de catégorie existe déjà.',
            'slug.unique'  => 'Le slug doit être unique.',
            'status.in'    => 'Le statut doit être soit "active" soit "inactive".',
            'description.max' => 'La description ne peut pas avoir plus de 255 caractères.',
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
