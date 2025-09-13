<?php

namespace App\Http\Requests\ingredient;

use App\Models\Supplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
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
        $routeParam = $this->route('supplier') ?? $this->route('id');
        $supplierId = $routeParam instanceof Supplier ? $routeParam->id : $routeParam;

        return [
            'nom'            => ['required','string','max:255'],
            'contact_person' => ['required','string','max:255'],
            'email'          => [
                'required','email','max:255',
                Rule::unique('suppliers', 'email')->ignore($supplierId)
            ],
            'telephone'      => [
                'nullable','string','max:20',
                Rule::unique('suppliers', 'telephone')->ignore($supplierId)
            ],
            'adresse'        => ['nullable','string','max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required'            => 'Le nom est obligatoire.',
            'contact_person.required' => 'Le contact person est obligatoire.',
            'email.required'          => 'L\'email est obligatoire.',
            'email.email'             => 'L\'email doit être une adresse email valide.',
            'email.unique'            => 'Cet email est déjà utilisé.',
            'telephone.max'           => 'Le numéro de téléphone ne peut pas avoir plus de 20 caractères.',
            'telephone.unique'        => 'Ce numéro de téléphone est déjà utilisé.',
            'adresse.max'             => 'L\'adresse ne peut pas avoir plus de 500 caractères.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return response()->json([
            'status' => false,
            'message' => 'Erreur lors de la création du fournisseur.',
            'error' => $validator->errors()
        ], 422);
    }

    
}
