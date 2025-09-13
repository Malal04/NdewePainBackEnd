<?php

namespace App\Http\Requests\panier;

use Illuminate\Foundation\Http\FormRequest;

class CommandeRequest extends FormRequest
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
            'mode_livraison' => ['required', 'in:livraison,ramassage'],
            'plage_horaire' => ['nullable', 'string'],
            'adresse_id' => ['nullable', 'exists:addresses,id'],
            'methode_paiement' => ['required', 'in:carte,paiement_a_la_livraison,wave,orange_money'],
        ];
    }
}
