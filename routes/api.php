<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\adresse\AdresseController;
use App\Http\Controllers\Api\adresse\StatController;
use App\Http\Controllers\Api\ingredient\IngredientController;
use App\Http\Controllers\Api\ingredient\IngredientOrderController;
use App\Http\Controllers\Api\ingredient\SupplierController;
use App\Http\Controllers\Api\panier\CartController;
use App\Http\Controllers\Api\panier\PanierController;
use App\Http\Controllers\Api\produit\CategorieController;
use App\Http\Controllers\Api\produit\ProduitController;
use App\Http\Controllers\Api\stock\PromotionController;
use App\Http\Controllers\Api\stock\StockController;
use App\Http\Controllers\Api\adresse\RapportController;
use App\Http\Controllers\Api\adresse\SupportController;

Route::get('/api/documentation', function () {
    return view('l5-swagger::index');
});

Route::get('v1/dashboard/stats', [StatController::class, 'getStats'])->middleware('auth:api');

Route::prefix('v1/support')->group(function () {
    Route::post('/chat', [SupportController::class, 'createChat'])->middleware('auth:api');
    Route::get('/chats', [SupportController::class, 'getChats'])->middleware('auth:api');
    Route::post('/chat/{chatId}/message', [SupportController::class, 'sendMessage'])->middleware('auth:api');
});

Route::prefix('v1/rapports')->group(function () {
    Route::get('/', [RapportController::class, 'index']) ->middleware('auth:api'); 
    Route::get('/tendances', [RapportController::class, 'tendances']) ->middleware('auth:api'); 
    Route::get('/produits', [RapportController::class, 'produits']) ->middleware('auth:api'); 
    Route::get('/depenses', [RapportController::class, 'depenses']) ->middleware('auth:api'); 
    Route::get('/export', [RapportController::class, 'export']) ->middleware('auth:api'); 
});

Route::prefix('v1/auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refreshToken']);
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
    Route::get('users', [AuthController::class, 'listUsers'])->middleware('auth:api');
    Route::get('users/{id}', [AuthController::class, 'showUser'])->middleware('auth:api');
    Route::post('users/{id}/account-state', [AuthController::class, 'changeAccountState'])->middleware('auth:api');
    Route::post('update-profile', [AuthController::class, 'updateProfile'])->middleware('auth:api');
    Route::post('change-password', [AuthController::class, 'changePassword'])->middleware('auth:api');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::get('/employes', [AuthController::class, 'listEmployesEtGerants'])->middleware('auth:api');
});

Route::middleware('auth:api')->group(function () {
    Route::get('v1/adresses', [AdresseController::class, 'index']);
    Route::get('v1/adresses/principale', [AdresseController::class, 'getAdressePrincipale']);
    Route::get('v1/adresses/mode-livraison', [AdresseController::class, 'getModeLivraisonActuel']);
    Route::get('v1/adresses/user/{id}', [AdresseController::class, 'listByUser']);
    Route::get('v1/adresses/{id}', [AdresseController::class, 'show']);
    Route::post('v1/adresses', [AdresseController::class, 'store']);
    Route::post('v1/adresses/choisir/{id}', [AdresseController::class, 'choisirAdresse']);
    Route::post('v1/adresses/{id}/principale', [AdresseController::class, 'setAsPrincipale']);
    Route::post('v1/adresses/choisir/retrait', [AdresseController::class, 'choisirRetrait']);
    Route::put('v1/adresses/{id}', [AdresseController::class, 'update']);
    Route::delete('v1/adresses/{id}', [AdresseController::class, 'destroy']);
});

Route::prefix('v1')->middleware('auth:api')->group(function () {
    
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategorieController::class, 'index']);
        Route::get('/all', [CategorieController::class, 'indexDefault']);  
        Route::post('/', [CategorieController::class, 'store']);           
        Route::get('/{id}', [CategorieController::class, 'show']);         
        Route::put('/{id}', [CategorieController::class, 'update']);       
        Route::delete('/{id}', [CategorieController::class, 'destroy']);   
        Route::patch('/toggle-status/{id}', [CategorieController::class, 'toggleStatus']); 
        Route::get('/search', [CategorieController::class, 'search']);     
        Route::get('/slug/{slug}', [CategorieController::class, 'getBySlug']);
    });

    Route::prefix('produits')->group(function () {
        Route::get('/', [ProduitController::class, 'index']);
        Route::get('/all', [ProduitController::class, 'indexPagination']);
        Route::get('/filter', [ProduitController::class, 'filter']);
        Route::post('/', [ProduitController::class, 'store']);
        Route::get('/search', [ProduitController::class, 'search']);
        Route::get('/{id}', [ProduitController::class, 'show']);
        Route::get('/{id}/detail', [ProduitController::class, 'showWithRelated']);
        Route::put('/{id}', [ProduitController::class, 'update']);
        Route::delete('/{id}', [ProduitController::class, 'destroy']);
        Route::patch('/{id}/toggle-status', [ProduitController::class, 'toggleStatus']);
        Route::get('/slug/{slug}', [ProduitController::class, 'getBySlug']);
    });

    Route::prefix('ingredients')->middleware('auth:api')->group(function () {
        Route::get('/', [IngredientController::class, 'index']);
        Route::get('/pagination', [IngredientController::class, 'indexPagination']);
        Route::post('/', [IngredientController::class, 'store']);
        Route::get('/search', [IngredientController::class, 'search']);
        Route::get('/{ingredient}', [IngredientController::class, 'show']);
        Route::put('/{ingredient}', [IngredientController::class, 'update']);
        Route::delete('/{ingredient}', [IngredientController::class, 'destroy']);
        Route::patch('/{ingredient}/toggle-status', [IngredientController::class, 'toggleStatus']);
    });

    Route::prefix('suppliers')->middleware('auth:api')->group(function () {
        Route::get('/', [SupplierController::class, 'index']);
        Route::get('/pagination', [SupplierController::class, 'indexPagination']);
        Route::post('/', [SupplierController::class, 'store']);
        Route::get('/search', [SupplierController::class, 'search']);
        Route::get('/{supplier}', [SupplierController::class, 'show']);
        Route::put('/{supplier}', [SupplierController::class, 'update']);
        Route::delete('/{supplier}', [SupplierController::class, 'destroy']);
    });

    Route::prefix('ingredient-orders')->middleware('auth:api')->group(function () {
        Route::get('/', [IngredientOrderController::class, 'index']);
        Route::get('/pagination', [IngredientOrderController::class, 'indexPagination']);
        Route::post('/', [IngredientOrderController::class, 'store']);
        Route::get('/search', [IngredientOrderController::class, 'search']);
        Route::get('/{order}', [IngredientOrderController::class, 'show']);
        Route::put('/{order}', [IngredientOrderController::class, 'update']);
        Route::delete('/{order}', [IngredientOrderController::class, 'destroy']);
        Route::patch('/{order}/delivered', [IngredientOrderController::class, 'markAsDelivered']);
        Route::patch('/{order}/cancelled', [IngredientOrderController::class, 'markAsCancelled']);
    });

    Route::prefix('stocks')->group(function () {
        Route::get('/', [StockController::class, 'index']);
        Route::get('/{stock}', [StockController::class, 'show']);
        Route::post('/', [StockController::class, 'store']);
        Route::put('/{stock}', [StockController::class, 'update']);
        Route::delete('/{stock}', [StockController::class, 'destroy']);
    
        Route::post('/move', [StockController::class, 'move']);
        Route::get('/{produitId}/history', [StockController::class, 'history']);
    });

    Route::prefix('promotions')->group(function () {
        Route::get('/', [PromotionController::class, 'index']);
        Route::get('/toggle-status/{promotion}', [PromotionController::class, 'toggleStatus']);
        Route::post('/', [PromotionController::class, 'store']);
        Route::get('/{promotion}', [PromotionController::class, 'show']);
        Route::put('/{promotion}', [PromotionController::class, 'update']);
        Route::delete('/{promotion}', [PromotionController::class, 'destroy']);
    });

    Route::prefix('paniers')->group(function () {
        Route::get('/', [PanierController::class, 'voir']);
        Route::post('ajouter', [PanierController::class, 'addItem']);
        Route::put('mettre-a-jour/{itemId}', [PanierController::class, 'updateItem']);
        Route::delete('supprimer/{itemId}', [PanierController::class, 'removeItem']);
        Route::post('checkout', [PanierController::class, 'checkout']);
    });

    Route::prefix('carts')->group(function(){
        Route::get('/', [CartController::class, 'show']);
        Route::post('/items', [CartController::class, 'add']);
        Route::put('/items/{item}', [CartController::class, 'update']);
        Route::delete('/items/{item}', [CartController::class, 'remove']);
        Route::delete('/', [CartController::class, 'clear']);
        Route::post('/promo', [CartController::class, 'applyPromo']);
        Route::post('/set-delivery', [CartController::class, 'setDelivery']);
        Route::post('/confirm', [CartController::class, 'confirm']);
        Route::get('/mes-commandes', [CartController::class, 'mesCommandes']);
        Route::get('/mes-commandes/{id}', [CartController::class, 'detailCommande']);
        Route::get('/commandes', [CartController::class, 'allCommandes']);
        Route::patch('/commandes/{id}/statut', [CartController::class, 'updateStatutCommande']);
    });


});
