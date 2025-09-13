<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code_commande')->nullable()->unique();
            $table->enum('mode_livraison', ['livraison', 'ramassage'])->default('livraison');
            $table->decimal('sous_total', 10, 2)->default(0);
            $table->decimal('frais_livraison', 10, 2)->default(0);
            $table->decimal('remise', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('plage_horaire')->nullable();
            $table->enum('statut_commande', [
                'en_attente', 
                'confirmee', 
                'en_cours', 
                'livree', 
                'annulee'
            ])->default('en_attente');
            $table->foreignId('adresse_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
