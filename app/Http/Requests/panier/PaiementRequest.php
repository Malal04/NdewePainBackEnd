<?php

namespace App\Http\Requests\panier;

use Illuminate\Foundation\Http\FormRequest;

class PaiementRequest extends FormRequest
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
            'commande_id' => ['required', 'exists:commandes,id'],
            'methode' => ['required', 'in:carte,paiement_a_la_livraison,wave,orange_money'],
            'statut' => ['required', 'in:en_attente,paye,echec,rembourse'],
            'transaction_id' => ['nullable', 'string'],
            'montant' => ['required', 'numeric', 'min:0'],
        ];
    }
}
