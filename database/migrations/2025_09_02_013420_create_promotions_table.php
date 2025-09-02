<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->enum(
                'type_remise', [
                    'pourcentage', 'montant', 'bogo', 'gratuit_livraison'
                ]
            );
            $table->decimal('valeur_remise', 10, 2)->nullable();
            $table->string('code_promo')->nullable()->unique();
            $table->text('conditions')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->string('recurrence')->nullable();
            $table->enum(
                'status',
                ['active', 'inactive']
            )->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
