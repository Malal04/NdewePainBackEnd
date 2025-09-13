<?php

namespace App\Http\Controllers\Api\adresse;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RapportController extends Controller
{
    // Récupération des stats globales
    public function index(Request $request)
    {
        $periode = $request->get('periode', 'mensuel');

        // Filtre de période
        $dateDebut = match ($periode) {
            'mensuel'     => Carbon::now()->startOfMonth(),
            'trimestriel' => Carbon::now()->subMonths(3),
            'annuel'      => Carbon::now()->startOfYear(),
            default       => Carbon::now()->startOfMonth(),
        };

        $commandes = Commande::where('created_at', '>=', $dateDebut)->get();

        return response()->json([
            'revenu_total'      => $commandes->sum('total'),
            'commandes_totales' => $commandes->count(),
            'valeur_moyenne'    => $commandes->avg('total'),
        ]);
    }

    // Graphique des tendances (revenus par jour sur 30 jours)
    public function tendances(Request $request)
    {
        $dateDebut = Carbon::now()->subDays(30);

        $data = Commande::selectRaw('DATE(created_at) as date, SUM(total) as revenu, COUNT(*) as commandes')
            ->where('created_at', '>=', $dateDebut)
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($data);
    }

    // Produits les plus vendus
    public function produits(Request $request)
{
    $data = DB::table('article_commandes')
        ->join('produits', 'produits.id', '=', 'article_commandes.produit_id')
        ->join('commandes', 'commandes.id', '=', 'article_commandes.commande_id')
        ->select('produits.id', 'produits.nom')
        ->selectRaw('SUM(article_commandes.quantite) as quantite_vendue, SUM(article_commandes.quantite * produits.prix_unitaire) as revenu')
        ->groupBy('produits.id', 'produits.nom')
        ->orderByDesc('quantite_vendue')
        ->limit(5)
        ->get();

    return response()->json($data);
}

    
    // Export PDF ou Excel (placeholder)
    public function export()
    {
        return response()->json(['message' => 'Export PDF en cours...']);
    }

}
