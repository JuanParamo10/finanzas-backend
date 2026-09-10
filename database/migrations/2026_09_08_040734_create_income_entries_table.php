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
        // Un registro por cada vez que reportas ganancia (quincena de sueldo, día de InDrive)
        Schema::create('income_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('income_profile_id')->constrained();
            $table->decimal('gross_amount', 14, 2);   // lo que cobró el sistema / el sueldo bruto
            $table->decimal('gas_expense', 14, 2)->nullable(); // solo InDrive: gasolina real gastada
            $table->date('entry_date');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income_entries');
    }
};
