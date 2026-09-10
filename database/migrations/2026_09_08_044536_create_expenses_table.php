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
        // Plata que sale de un sobre (gastos reales), a diferencia de
        // allocations/extra_incomes que es plata que entra.
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('envelope_id')->constrained();
            $table->decimal('amount', 14, 2);
            $table->string('description');   // el "por qué" del gasto, obligatorio
            $table->date('entry_date');
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
