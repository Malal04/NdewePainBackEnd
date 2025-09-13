<?php

namespace App\Http\Controllers\Api\panier;

use App\Http\Controllers\Controller;
use App\Http\Requests\panier\CommandeRequest;
use App\Http\Resources\panier\CommandeResource;
use App\Models\Commande;
use App\Services\panier\CommandeService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CommandeController extends Controller
{
    protected CommandeService $commandeService;

    public function __construct(CommandeService $commandeService)
    {
        $this->commandeService = $commandeService;
    }

    /**
     * Confirme la commande
     */
    public function confirm(CommandeRequest $request)
    {
        try {
            $commande = $this->commandeService->confirmOrder($request->validated());

            return (new CommandeResource($commande))->additional([
                'meta' => [
                    'status' => true,
                    'message' => 'Commande confirmée avec succès.',
                ]
            ]);

        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Liste des commandes utilisateur
     */
    public function index()
    {
        $user = Auth::user();
        $commandes = Commande::with(['articlesCommande.produit', 'paiement'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return CommandeResource::collection($commandes)->additional([
            'meta' => ['status' => true]
        ]);
    }

    /**
     * Détail d’une commande
     */
    public function show($id)
    {
        $user = Auth::user();
        $commande = Commande::with(['articlesCommande.produit', 'paiement'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return (new CommandeResource($commande))->additional([
            'meta' => ['status' => true]
        ]);
    }
    
}
