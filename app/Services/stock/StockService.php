<?php

namespace App\Services\stock;

use App\Http\Requests\stock\StockMoveRequest;
use App\Http\Requests\stock\StockRequest;
use App\Http\Resources\stock\StockHistoryResource;
use App\Http\Resources\stock\StockResource;
use App\Models\Stock;
use App\Models\StockHistory;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockService
{
    use ApiResponse;

    /** Liste paginée */
    public function index($request)
    {
        $perPage = $request->get('per_page', 15);
        $stocks = Stock::with('produit')->paginate($perPage);

        return $this->success(
            StockResource::collection($stocks),
            'Liste des stocks',
            200,
            $this->meta($stocks)
        );
    }

    /** Détails */
    public function show(Stock $stock)
    {
        return $this->success(
            new StockResource($stock->load('produit')),
            "Détails du stock #{$stock->id}"
        );
    }

    /** Création */
    public function store(StockRequest $request)
    {
        $stock = Stock::create($request->validated());
        $this->recomputeStatus($stock);

        return $this->success(
            new StockResource($stock->load('produit')),
            'Stock créé avec succès',
            201
        );
    }

    /** Mise à jour */
    public function update(StockRequest $request, Stock $stock)
    {
        $stock->update($request->validated());
        $this->recomputeStatus($stock);

        return $this->success(
            new StockResource($stock->load('produit')),
            "Stock mis à jour"
        );
    }

    /** Suppression */
    public function destroy(Stock $stock)
    {
        $stock->delete();
        return $this->success(null, "Stock supprimé");
    }

    /** Mouvement de stock */
    public function move(StockMoveRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $stock = Stock::firstOrCreate(['produit_id' => $request->produit_id]);

            if ($request->has('seuil_minimum')) {
                $stock->seuil_minimum = $request->seuil_minimum;
            }

            match ($request->type_mouvement) {
                StockHistory::ENTREE    => $stock->quantite_actuelle += $request->quantite,
                StockHistory::SORTIE    => $stock->quantite_actuelle -= $request->quantite,
                StockHistory::AJUSTEMENT=> $stock->quantite_actuelle = $request->quantite,
            };

            $stock->save();
            $this->recomputeStatus($stock);

            StockHistory::create([
                'produit_id'     => $request->produit_id,
                'type_mouvement' => $request->type_mouvement,
                'quantite'       => $request->quantite,
                'user_id'        => Auth::id(),
                'commentaire'    => $request->commentaire,
            ]);

            return $this->success(
                new StockResource($stock->load('produit')),
                'Mouvement de stock effectué'
            );
        });
    }

    /** Historique */
    public function history($produitId, $request)
    {
        $perPage = $request->get('per_page', 15);

        $histories = StockHistory::where('produit_id', $produitId)
            ->with('produit')
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return $this->success(
            StockHistoryResource::collection($histories),
            "Historique des mouvements du produit #$produitId",
            200,
            $this->meta($histories)
        );
    }

    /** Recalcule le statut */
    public function recomputeStatus(Stock $stock): Stock
    {
        $stock->statut = match (true) {
            $stock->quantite_actuelle <= 0 => Stock::OUT_OF_STOCK,
            $stock->quantite_actuelle <= $stock->seuil_minimum => Stock::LOW_STOCK,
            default => Stock::IN_STOCK,
        };
        $stock->save();
        return $stock->refresh();
    }

    /** Pagination meta */
    private function meta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
        ];
    }
}
