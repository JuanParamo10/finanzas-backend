<?php

namespace App\Services;

use App\Models\Allocation;
use App\Models\AllocationRule;
use App\Models\Envelope;
use App\Models\ExtraIncome;
use App\Models\FixedExpense;
use App\Models\IncomeEntry;

class IncomeAllocationService
{
    // Sobre especial: recibe el residuo de redondear los porcentajes, para
    // que ningún otro sobre termine con números feos (ni decimales, ni
    // cifras sueltas como 16.029) — todo cae a múltiplos de ROUND_STEP.
    private const ALCANCIA = 'Alcancía';

    private const ROUND_STEP = 500;

    public function registerSueldo(float $grossAmount, string $date, ?string $description = null): IncomeEntry
    {
        $entry = IncomeEntry::create([
            'income_profile_id' => 1, // Sueldo
            'gross_amount' => $grossAmount,
            'entry_date' => $date,
            'description' => $description,
        ]);

        $arriendo = FixedExpense::where('name', 'Arriendo')->first();
        $restante = $grossAmount - $arriendo->amount;

        $this->allocateByName($entry, 'Arriendo', $arriendo->amount, 'fixed_deduction');
        $residuo = $this->applyPercentages($entry, 'sueldo', $restante);
        $this->depositResidual($entry, $residuo);

        return $entry->load('allocations.envelope');
    }

    public function registerIndrive(float $grossAmount, float $gasExpense, float $appFee, string $date, ?string $description = null): IncomeEntry
    {
        $entry = IncomeEntry::create([
            'income_profile_id' => 2, // InDrive
            'gross_amount' => $grossAmount,
            'gas_expense' => $gasExpense,
            'app_fee' => $appFee,
            'entry_date' => $date,
            'description' => $description,
        ]);

        $parqueadero = FixedExpense::where('name', 'Parqueadero')->first();
        // Redondeada al múltiplo de 500 más cercano de una vez — y usada tal
        // cual para calcular el restante, para que la plata cuadre exacto.
        $cuotaDiaria = $this->roundToStep($parqueadero->amount / 7);

        $restante = $grossAmount - $gasExpense - $appFee - $cuotaDiaria;

        // Nota: la gasolina y la comisión de la app NO se registran como
        // Allocation contra un sobre — ya quedan guardadas en
        // income_entries.gas_expense / app_fee (para el dashboard). El
        // dinero simplemente sale del bruto antes de repartir el resto.
        $this->allocateByName($entry, 'Parqueadero', $cuotaDiaria, 'fixed_deduction');
        $residuo = $this->applyPercentages($entry, 'indrive', $restante);
        $this->depositResidual($entry, $residuo);

        return $entry->load('allocations.envelope');
    }

    // Ingreso extra con destino manual: el 100% va a un solo sobre elegido.
    public function registerExtra(float $amount, int $envelopeId, string $date, ?string $description = null): ExtraIncome
    {
        return ExtraIncome::create([
            'envelope_id' => $envelopeId,
            'amount' => $amount,
            'entry_date' => $date,
            'description' => $description,
        ]);
    }

    // Ingreso extra sin destino elegido: se reparte solo entre varios sobres
    // según las allocation_rules del set 'extra' (una fila de extra_incomes
    // por sobre, mismo día/descripción). El residuo de redondeo va a Alcancía.
    public function registerExtraSplit(float $amount, string $date, ?string $description = null): array
    {
        $rules = AllocationRule::where('rule_set', 'extra')->orderBy('sort_order')->get();
        $asignado = 0;
        $creados = [];

        foreach ($rules as $rule) {
            $monto = $this->roundToStep($amount * $rule->percentage);
            $asignado += $monto;
            $creados[] = $this->createExtraForEnvelope($monto, $rule->envelope_id, $date, $description);
        }

        $residuo = round($amount - $asignado);
        if ($residuo !== 0.0) {
            $alcancia = Envelope::where('name', self::ALCANCIA)->firstOrFail();
            $creados[] = $this->createExtraForEnvelope($residuo, $alcancia->id, $date, $description);
        }

        return $creados;
    }

    private function createExtraForEnvelope(float $amount, int $envelopeId, string $date, ?string $description): ExtraIncome
    {
        return ExtraIncome::create([
            'envelope_id' => $envelopeId,
            'amount' => $amount,
            'entry_date' => $date,
            'description' => $description,
        ])->load('envelope');
    }

    // Aplica el reparto por porcentaje de un rule_set (sueldo/indrive/extra),
    // leído de allocation_rules — editable desde la app en "Gestionar sobres".
    // Cada sobre recibe un monto redondeado al múltiplo de ROUND_STEP más
    // cercano (números limpios, ej. 16.000 en vez de 16.029); lo que sobra o
    // falta por esa aproximación se devuelve para que el llamador lo mande a Alcancía.
    private function applyPercentages(IncomeEntry $entry, string $ruleSet, float $base): float
    {
        $rules = AllocationRule::where('rule_set', $ruleSet)->orderBy('sort_order')->get();
        $asignado = 0;

        foreach ($rules as $rule) {
            $monto = $this->roundToStep($base * $rule->percentage);
            $asignado += $monto;
            $this->allocateById($entry, $rule->envelope_id, $monto, 'percentage');
        }

        return round($base - $asignado);
    }

    // Redondea al múltiplo de ROUND_STEP más cercano (ej. 16.290 → 16.500,
    // 16.029 → 16.000), para que los montos repartidos sean números limpios.
    private function roundToStep(float $amount): float
    {
        return round($amount / self::ROUND_STEP) * self::ROUND_STEP;
    }

    private function depositResidual(IncomeEntry $entry, float $residuo): void
    {
        if ($residuo === 0.0) {
            return;
        }

        $this->allocateByName($entry, self::ALCANCIA, $residuo, 'rounding');
    }

    private function allocateByName(IncomeEntry $entry, string $envelopeName, float $amount, string $type): void
    {
        $envelope = Envelope::where('name', $envelopeName)->firstOrFail();
        $this->allocateById($entry, $envelope->id, $amount, $type);
    }

    private function allocateById(IncomeEntry $entry, int $envelopeId, float $amount, string $type): void
    {
        Allocation::create([
            'income_entry_id' => $entry->id,
            'envelope_id' => $envelopeId,
            'amount' => $amount,
            'type' => $type,
        ]);
    }
}
