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
        Schema::table('income_entries', function (Blueprint $table) {
            // Comisión que retiene InDrive sobre lo producido en el día (solo
            // aplica a entradas de InDrive). Igual que gas_expense: es plata
            // que ya se usó, no se reparte a ningún sobre.
            $table->decimal('app_fee', 14, 2)->nullable()->after('gas_expense');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('income_entries', function (Blueprint $table) {
            $table->dropColumn('app_fee');
        });
    }
};
