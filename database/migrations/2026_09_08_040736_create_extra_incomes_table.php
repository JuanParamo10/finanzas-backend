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
        // Ingresos irregulares: regalos de tus papás, etc.
        Schema::create('extra_incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('envelope_id')->constrained();  // a qué sobre va el 100%
            $table->decimal('amount', 14, 2);
            $table->string('description')->nullable();
            $table->date('entry_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extra_incomes');
    }
};
