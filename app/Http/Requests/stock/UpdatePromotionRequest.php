<?php

namespace App\Http\Requests\stock;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePromotionRequest extends FormRequest
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
        $promotionId = $this->route('promotion')?->id; // récupère l'ID depuis la route

        return [
            'nom' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string'],
            'type_remise' => ['required', 'in:pourcentage,montant,bogo,gratuit_livraison'],
            'valeur_remise' => ['nullable', 'numeric', 'min:0'],
            'code_promo' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('promotions', 'code_promo')->ignore($promotionId)
            ],
            'conditions' => ['nullable', 'string'],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'recurrence' => ['nullable', 'string', 'max:190'],
            'status' => ['nullable', 'in:active,inactive'],
            'produit_ids' => ['array'],
            'produit_ids.*' => ['integer', 'exists:produits,id'],
        ];
    }
}
