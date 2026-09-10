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
        // Arriendo y Parqueadero: obligaciones que se descuentan ANTES de aplicar %
        Schema::create('fixed_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('income_profile_id')->constrained();
            $table->string('name');                  // "Arriendo", "Parqueadero"
            $table->decimal('amount', 14, 2);        // monto por periodo
            $table->enum('frequency', ['daily', 'weekly', 'biweekly', 'monthly']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixed_expenses');
    }
};
