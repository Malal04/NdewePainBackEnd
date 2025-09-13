<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'promo_code',
        'remise_appliquee'
    ];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function addItem($produitId, $quantite)
    {
        $produit = Produit::findOrFail($produitId);

        // Vérifier la disponibilité en stock
        if ($produit->stock->quantite_actuelle < $quantite) {
            throw new \Exception('Quantité insuffisante en stock');
        }

        // Ajouter ou mettre à jour l'article dans le panier
        $item = $this->items()->updateOrCreate(
            ['produit_id' => $produitId],
            [
                'quantite' => $quantite,
                'prix_unitaire' => $produit->prix_unitaire,
                'total' => $produit->prix_unitaire * $quantite
            ]
        );

        // Mise à jour du stock
        $this->updateStock(
            $produitId, 
            $quantite
        );
    }

    public function updateItemQuantity($itemId, $quantite)
    {
        $item = $this->items()->findOrFail($itemId);
        $produit = $item->produit;

        // Vérifier la disponibilité en stock
        if ($produit->stock->quantite_actuelle < $quantite) {
            throw new \Exception('Quantité insuffisante en stock');
        }

        // Mise à jour de l'article du panier
        $item->update([
            'quantite' => $quantite,
            'total' => $item->prix_unitaire * $quantite
        ]);

        // Mise à jour du stock
        $this->updateStock(
            $produit->id,
            $quantite - $item->quantite
        );
    }

    public function removeItem($itemId)
    {
        $item = $this->items()->findOrFail($itemId);
        $produit = $item->produit;

        // Supprimer l'article du panier
        $item->delete();

        // Réajuster le stock
        $this->updateStock(
            $produit->id,
            -$item->quantite
        );
    }

    private function updateStock($produitId, $quantiteChange)
    {
        $stock = Stock::where(
            'produit_id', 
            $produitId
        )->first();
        $stock->quantite_actuelle -= $quantiteChange;
        $stock->save();

        StockHistory::create([
            'produit_id' => $produitId,
            'user_id'    => Auth::id(),
            'type_mouvement' => $quantiteChange > 0 ? 'sortie' : 'entree',
            'quantite' => abs($quantiteChange),
            'commentaire' => 'Mise à jour du stock pour le panier'
        ]);
    }

    public function calculateTotal()
    {
        return $this->items()->sum('total');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
