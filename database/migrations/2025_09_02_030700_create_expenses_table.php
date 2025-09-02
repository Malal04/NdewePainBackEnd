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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_report_id')->nullable()->constrained('financial_reports')->onDelete('set null');
            $table->enum(
                'categorie',
                ['rent', 'supplies', 'salaries', 'marketing', 'utilities', 'other']
            );
            $table->decimal('montant', 12, 2);
            $table->date('date_depense');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
