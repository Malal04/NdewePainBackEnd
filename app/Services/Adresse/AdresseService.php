<?php

namespace App\Services\adresse;

use App\Http\Resources\adresse\AdresseResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\adresse\AdresseRequest;
use App\Models\Addresses;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class AdresseService
{

    public function listAdresses(Request $request)
    {
        $user = Auth::user();

        $query = Addresses::query()->with('user');

        // Client → uniquement ses adresses
        if ($user->role === 'client') {
            $query->where('user_id', $user->id);
        } else {
            // Admin/Gérant/Employé → peut filtrer
            if ($request->has('ville')) {
                $query->where('ville', 'like', '%' . $request->ville . '%');
            }
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }
            if ($request->has('mode_livraison')) {
                $query->where('mode_livraison', $request->mode_livraison);
            }
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }
        }

        // Pagination (par défaut 10 par page)
        $perPage = $request->get('per_page', 10);
        $adresses = $query->latest()->paginate($perPage);

        return response()->json([
            'status'   => true,
            'adresses' => AdresseResource::collection($adresses),
            'meta'     => [
                'current_page' => $adresses->currentPage(),
                'last_page'    => $adresses->lastPage(),
                'per_page'     => $adresses->perPage(),
                'total'        => $adresses->total(),
            ],
        ]);
    }

    public function createAdresse(AdresseRequest $request)
    {
        // Si est_principale = true, reset toutes les autres
        if ($request->est_principale) {
            Addresses::where('user_id', Auth::id())
                ->update(['est_principale' => false]);
        }

        $adresse = Addresses::create([
            'user_id'       => Auth::id(),
            'ligne_adresse' => $request->ligne_adresse,
            'ville'         => $request->ville,
            'code_postal'   => $request->code_postal,
            'pays'          => $request->pays ?? 'Sénégal',
            'est_principale'=> $request->est_principale ?? false,
            'type'          => $request->type ?? 'maison',
            'mode_livraison' => $request->mode_livraison ?? 'livraison',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Adresse ajoutée avec succès.',
            'adresse' => new AdresseResource($adresse),
        ], 201);
    }

    public function updateAdresse(AdresseRequest $request, $id)
    {
        $adresse = Addresses::where('user_id', Auth::id())
            ->findOrFail($id);

        if ($request->has('est_principale') && $request->est_principale) {
            Addresses::where('user_id', Auth::id())
                ->update(['est_principale' => false]);
        }

        $adresse->update([
            'ligne_adresse' => $request->ligne_adresse ?? $adresse->ligne_adresse,
            'ville'         => $request->ville ?? $adresse->ville,
            'code_postal'   => $request->code_postal ?? $adresse->code_postal,
            'pays'          => $request->pays ?? $adresse->pays,
            'est_principale'=> $request->est_principale ?? $adresse->est_principale,
            'type'          => $request->type ?? $adresse->type,
            'mode_livraison' => $request->mode_livraison ?? $adresse->mode_livraison,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Adresse mise à jour avec succès.',
            'adresse' => new AdresseResource($adresse),
        ]);
    }

    public function deleteAdresse($id)
    {
        $adresse = Addresses::where(
            'user_id', 
            Auth::id()
        )->findOrFail($id);
        $adresse->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Adresse supprimée avec succès.',
        ]);
    }

    public function choisirAdresseLivraison($adresseId)
    {
        $adresse = Addresses::where(
            'user_id', 
            Auth::id()
        )->find($adresseId);

        if (!$adresse) {
            throw ValidationException::withMessages([
                'adresse' => ['Adresse introuvable.'],
            ]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Adresse de livraison sélectionnée.',
            'adresse' => new AdresseResource($adresse),
        ]);
    }

    public function setAsPrincipale($id)
    {
        $userId = Auth::id();

        $adresse = Addresses::where('user_id', $userId)
            ->findOrFail($id);

        // Met toutes les autres adresses en false
        Addresses::where('user_id', $userId)
            ->update(['est_principale' => false]);

        // Met celle-ci en true
        $adresse->update(['est_principale' => true]);

        return response()->json([
            'status'  => true,
            'message' => 'Adresse définie comme principale avec succès.',
            'adresse' => new AdresseResource($adresse),
        ]);
    }

    public function getAdressePrincipale()
    {
        $adresse = Addresses::where('user_id', Auth::id())
            ->where('est_principale', true)
            ->first();

        if (!$adresse) {
            return response()->json([
                'status'  => false,
                'message' => 'Aucune adresse principale définie.',
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'adresse' => new AdresseResource($adresse),
        ]);
    }

    public function choisirRetrait()
    {
        $userId = Auth::id();

        // On supprime les anciens choix "retrait" si existants
        Addresses::where('user_id', $userId)
            ->where('mode_livraison', 'retrait')
            ->delete();

        // On crée une "adresse" spéciale pour le retrait
        $adresse = Addresses::create([
            'user_id'        => $userId,
            'ligne_adresse'  => 'Retrait en boulangerie',
            'ville'          => 'Boulangerie',
            'pays'           => 'Sénégal',
            'est_principale' => true,
            'type'           => 'autre',
            'mode_livraison' => 'retrait',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Mode de livraison défini sur retrait en boulangerie.',
            'mode'    => 'retrait',
            'adresse' => new AdresseResource($adresse),
        ]);
    }

    public function getModeLivraisonActuel()
    {
        $userId = Auth::id();

        // Vérifier d'abord si un retrait a été choisi
        $retrait = Addresses::where('user_id', $userId)
            ->where('mode_livraison', 'retrait')
            ->latest()
            ->first();

        if ($retrait) {
            return response()->json([
                'status'  => true,
                'mode'    => 'retrait',
                'message' => 'Le client a choisi le retrait en boulangerie.',
            ]);
        }

        // Sinon, vérifier l’adresse principale pour livraison
        $adressePrincipale = Addresses::where('user_id', $userId)
            ->where('mode_livraison', 'livraison')
            ->where('est_principale', true)
            ->first();

        if ($adressePrincipale) {
            return response()->json([
                'status'  => true,
                'mode'    => 'livraison',
                'adresse' => new AdresseResource($adressePrincipale),
                'message' => 'Le client a choisi la livraison à domicile.',
            ]);
        }

        return response()->json([
            'status'  => false,
            'message' => 'Aucun mode de livraison défini.',
        ], 404);
    }

    public function showAdresse($id)
    {
        $user = Auth::user();

        if (in_array($user->role, ['admin', 'gerant', 'employe'])) {
            $adresse = Addresses::with('user')->findOrFail($id);
        } else {
            $adresse = Addresses::where('user_id', $user->id)->findOrFail($id);
        }

        return response()->json([
            'status'  => true,
            'adresse' => new AdresseResource($adresse),
        ]);
    }

    public function listAdressesByUser($userId)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'gerant', 'employe'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Accès non autorisé.',
            ], 403);
        }

        $adresses = Addresses::where('user_id', $userId)->get();

        return response()->json([
            'status'   => true,
            'adresses' => AdresseResource::collection($adresses),
        ]);
    }

}
