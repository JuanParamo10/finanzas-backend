<?php

namespace Database\Seeders;

use App\Models\AllocationRule;
use App\Models\Envelope;
use App\Models\FixedExpense;
use App\Models\IncomeProfile;
use Illuminate\Database\Seeder;

class FinanzasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sobres. Los de ahorro real (no los de gasto recurrente como Arriendo,
        // Parqueadero, Gastos básicos o Bolsillo) arrancan con una meta sugerida
        // — son puntos de partida razonables, no un cálculo exacto de tu vida:
        // edítalas cuando quieras desde la app.
        $goals = [
            'Mantenimiento preventivo' => 800000,
            'Ahorro papeles del carro' => 1200000,
            'Fondo de emergencia' => 3000000,
            'Ahorro/inversión' => 5000000,
        ];

        $envelopes = collect([
            'Arriendo', 'Gastos básicos', 'Parqueadero',
            'Mantenimiento preventivo', 'Ahorro papeles del carro',
            'Fondo de emergencia', 'Ahorro/inversión', 'Bolsillo',
            // Alcancía no entra en ningún reparto por porcentaje — recibe el
            // residuo de redondear los demás, para que nunca queden decimales sueltos.
            'Alcancía',
        ])->mapWithKeys(fn ($name) => [
            $name => Envelope::create(['name' => $name, 'goal_amount' => $goals[$name] ?? null])->id,
        ]);

        // Perfiles
        $sueldo = IncomeProfile::create(['name' => 'Sueldo', 'income_type' => 'fixed']);
        $indrive = IncomeProfile::create(['name' => 'InDrive', 'income_type' => 'variable']);

        // Gastos fijos (se descuentan ANTES del %)
        FixedExpense::create([
            'income_profile_id' => $sueldo->id, 'name' => 'Arriendo',
            'amount' => 365000, 'frequency' => 'biweekly',
        ]);
        FixedExpense::create([
            'income_profile_id' => $indrive->id, 'name' => 'Parqueadero',
            'amount' => 40000, 'frequency' => 'weekly',
        ]);

        // Reparto por porcentaje de cada tipo de ingreso — editable desde la
        // app (Gestionar sobres). El último de cada lista absorbe el residuo
        // de redondeo, por eso el orden importa.
        $rules = [
            'sueldo' => [
                'Gastos básicos' => 0.40,
                'Ahorro/inversión' => 0.30,
                'Bolsillo' => 0.30,
            ],
            'indrive' => [
                'Gastos básicos' => 0.05,
                'Arriendo' => 0.19,
                'Mantenimiento preventivo' => 0.08,
                'Ahorro papeles del carro' => 0.07,
                'Fondo de emergencia' => 0.10,
                'Ahorro/inversión' => 0.21,
                'Bolsillo' => 0.30,
            ],
            'extra' => [
                'Gastos básicos' => 0.15,
                'Ahorro/inversión' => 0.35,
                'Fondo de emergencia' => 0.20,
                'Bolsillo' => 0.30,
            ],
        ];

        foreach ($rules as $ruleSet => $envelopePercentages) {
            $order = 0;
            foreach ($envelopePercentages as $name => $percentage) {
                AllocationRule::create([
                    'rule_set' => $ruleSet,
                    'envelope_id' => $envelopes[$name],
                    'percentage' => $percentage,
                    'sort_order' => $order++,
                ]);
            }
        }
    }
}
