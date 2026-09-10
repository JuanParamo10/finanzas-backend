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
        Schema::table('envelopes', function (Blueprint $table) {
            // Meta de ahorro opcional (solo aplica a sobres de ahorro real,
            // no a los de gasto recurrente como Arriendo o Bolsillo).
            $table->decimal('goal_amount', 14, 2)->nullable()->after('icon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('envelopes', function (Blueprint $table) {
            $table->dropColumn('goal_amount');
        });
    }
};
