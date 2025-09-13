<?php

namespace App\Http\Controllers\Api\adresse;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Commande;
use App\Models\SalesSummary;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class StatController extends Controller
{
    // Méthode principale pour récupérer toutes les stats
    public function getStats(Request $request)
    {
        $periode = $request->input('periode', 'weekly');
        $categorieId = $request->input('categorie_id', null);

        if (!in_array($periode, ['daily', 'weekly', 'monthly'])) {
            return response()->json(['error' => 'Période invalide'], 400);
        }

        try {
            return response()->json([
                'revenu_total'      => $this->calculateTotalRevenue($periode, $categorieId),
                'commandes_totales' => $this->calculateTotalOrders($periode, $categorieId),
                'valeur_moyenne'    => $this->calculateAverageOrderValue($periode, $categorieId),
                'nouveaux_clients'  => $this->calculateNewClients($periode),
                'tendances_ventes'  => $this->calculateSalesTrends($periode, $categorieId),
                'produits_vendus'   => $this->getTopSellingProducts($periode),
                'depenses_clients'  => $this->getClientSpendingBreakdown($periode),
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des statistiques : ' . $e->getMessage());
            return response()->json(['error' => 'Erreur interne du serveur'], 500);
        }
    }

    // Revenu total
    private function calculateTotalRevenue($periode, $categorieId)
    {
        try {
            $query = Commande::query();

            if ($categorieId) {
                $query->whereHas('articlesCommande.produit', fn($q) => $q->where('categorie_id', $categorieId));
            }

            $this->applyPeriodFilter($query, $periode);
            return $query->sum('total');
        } catch (\Exception $e) {
            Log::error('Erreur revenu total : ' . $e->getMessage());
            return 0;
        }
    }

    // Nombre total de commandes
    private function calculateTotalOrders($periode, $categorieId)
    {
        try {
            $query = Commande::query();

            if ($categorieId) {
                $query->whereHas('articlesCommande.produit', fn($q) => $q->where('categorie_id', $categorieId));
            }

            $this->applyPeriodFilter($query, $periode);
            return $query->count();
        } catch (\Exception $e) {
            Log::error('Erreur commandes totales : ' . $e->getMessage());
            return 0;
        }
    }

    // Valeur moyenne d’une commande
    private function calculateAverageOrderValue($periode, $categorieId)
    {
        try {
            $revenue = $this->calculateTotalRevenue($periode, $categorieId);
            $orders = $this->calculateTotalOrders($periode, $categorieId);
            return $orders ? $revenue / $orders : 0;
        } catch (\Exception $e) {
            Log::error('Erreur valeur moyenne : ' . $e->getMessage());
            return 0;
        }
    }

    // Nouveaux clients
    private function calculateNewClients($periode)
    {
        try {
            $query = User::query();
            $this->applyPeriodFilter($query, $periode);
            return $query->count();
        } catch (\Exception $e) {
            Log::error('Erreur nouveaux clients : ' . $e->getMessage());
            return 0;
        }
    }

    // Tendances des ventes
    private function calculateSalesTrends($periode, $categorieId)
    {
        try {
            $query = SalesSummary::query();

            if ($categorieId) {
                $query->whereHas('produit', fn($q) => $q->where('categorie_id', $categorieId));
            }

            $this->applyPeriodFilter($query, $periode);

            $data = $query->groupBy('date')
                ->selectRaw('COALESCE(SUM(quantite_vendue),0) as total_quantite, COALESCE(SUM(revenu),0) as total_revenu, date')
                ->orderBy('date', 'desc')
                ->get();

            return $data->isEmpty() ? [[
                'date' => now()->toDateString(),
                'total_quantite' => 0,
                'total_revenu' => 0
            ]] : $data;
        } catch (\Exception $e) {
            Log::error('Erreur tendances ventes : ' . $e->getMessage());
            return [[
                'date' => now()->toDateString(),
                'total_quantite' => 0,
                'total_revenu' => 0
            ]];
        }
    }

    // Produits les plus vendus
    private function getTopSellingProducts($periode)
    {
        try {
            $query = SalesSummary::query();
            $this->applyPeriodFilter($query, $periode);

            $data = $query->orderByDesc('quantite_vendue')
                ->take(5)
                ->get(['produit_id', 'quantite_vendue', 'revenu']);

            return $data->isEmpty() ? [[
                'produit_id' => null,
                'quantite_vendue' => 0,
                'revenu' => 0
            ]] : $data;
        } catch (\Exception $e) {
            Log::error('Erreur produits vendus : ' . $e->getMessage());
            return [[
                'produit_id' => null,
                'quantite_vendue' => 0,
                'revenu' => 0
            ]];
        }
    }

    // Répartition des dépenses à partir des commandes
    private function getClientSpendingBreakdown($periode)
    {
        try {
            $query = Commande::query();
            $this->applyPeriodFilter($query, $periode);

            $data = $query->selectRaw('SUM(total) as total,
                CASE 
                    WHEN total < 50 THEN "< 50"
                    WHEN total BETWEEN 50 AND 100 THEN "50 - 100"
                    WHEN total BETWEEN 100 AND 150 THEN "100 - 150"
                    ELSE "> 150"
                END as range')
                ->groupBy('range')
                ->get();

            return $data->isEmpty() ? [
                ['total' => 0, 'range' => '< 50'],
                ['total' => 0, 'range' => '50 - 100'],
                ['total' => 0, 'range' => '100 - 150'],
                ['total' => 0, 'range' => '> 150'],
            ] : $data;
        } catch (\Exception $e) {
            Log::error('Erreur dépenses clients : ' . $e->getMessage());
            return [
                ['total' => 0, 'range' => '< 50'],
                ['total' => 0, 'range' => '50 - 100'],
                ['total' => 0, 'range' => '100 - 150'],
                ['total' => 0, 'range' => '> 150'],
            ];
        }
    }

    // Filtre période pour toutes les requêtes
    private function applyPeriodFilter($query, $periode)
    {
        if ($periode === 'daily') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($periode === 'weekly') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } else { // monthly
            $query->whereMonth('created_at', Carbon::now()->month);
        }
    }
} 