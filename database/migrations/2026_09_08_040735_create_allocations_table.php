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
        // El detalle de cómo quedó repartido cada income_entry
        Schema::create('allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('income_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('envelope_id')->constrained();
            $table->decimal('amount', 14, 2);
            $table->string('type');                  // 'percentage' | 'fixed_deduction'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allocations');
    }
};
