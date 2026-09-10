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
        // Reemplaza las constantes SUELDO_RULES/INDRIVE_RULES/EXTRA_RULES que
        // vivían hardcodeadas en IncomeAllocationService — ahora son editables
        // desde la app, para poder agregar sobres propios (ej. "Nequi") al reparto.
        Schema::create('allocation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_set'); // 'sueldo' | 'indrive' | 'extra'
            $table->foreignId('envelope_id')->constrained();
            $table->decimal('percentage', 6, 4); // 0.2000 = 20%
            $table->unsignedInteger('sort_order')->default(0); // el último de cada rule_set absorbe el residuo de redondeo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allocation_rules');
    }
};
