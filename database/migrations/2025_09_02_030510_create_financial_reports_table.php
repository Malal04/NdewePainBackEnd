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
        Schema::create('financial_reports', function (Blueprint $table) {
            $table->id();
            $table->enum(
                'periode',
                ['monthly', 'quarterly', 'yearly']
            );
            $table->date('date_debut');
            $table->date('date_fin');
            $table->decimal('revenus_totaux', 12, 2)->default(0);
            $table->decimal('depenses_totales', 12, 2)->default(0);
            $table->decimal('profit_total', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_reports');
    }
};
