<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Envelope;
use Illuminate\Http\Request;

class EnvelopeController extends Controller
{
    // Crea un sobre propio (ej. "Nequi — Recarga app"). No queda en ningún
    // reparto automático hasta que lo agregues desde "Gestionar sobres".
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:envelopes,name',
            'goal_amount' => 'nullable|numeric|min:0',
        ]);

        return Envelope::create($data);
    }

    public function balances()
    {
        // Saldo = lo que entró (allocations + extra_incomes) menos lo que ya se gastó (expenses).
        return Envelope::withSum('allocations', 'amount')
            ->withSum('extraIncomes', 'amount')
            ->withSum('expenses', 'amount')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->name,
                'goal_amount' => $e->goal_amount,
                'balance' => ($e->allocations_sum_amount ?? 0)
                    + ($e->extra_incomes_sum_amount ?? 0)
                    - ($e->expenses_sum_amount ?? 0),
            ]);
    }

    // Historial de movimientos de un sobre: mezcla allocations (reparto de sueldo/InDrive),
    // extra_incomes (regalos, ingresos irregulares) y expenses (plata que salió),
    // ordenado del más reciente al más viejo.
    public function history($id)
    {
        $envelope = Envelope::findOrFail($id);

        $allocations = $envelope->allocations()
            ->with('incomeEntry.incomeProfile')
            ->get()
            ->map(fn ($a) => [
                'date' => optional($a->incomeEntry)->entry_date,
                'amount' => $a->amount,
                'type' => $a->type,
                'source' => optional(optional($a->incomeEntry)->incomeProfile)->name,
            ]);

        $extras = $envelope->extraIncomes()
            ->get()
            ->map(fn ($e) => [
                'date' => $e->entry_date,
                'amount' => $e->amount,
                'type' => 'extra',
                'source' => $e->description,
            ]);

        $expenses = $envelope->expenses()
            ->get()
            ->map(fn ($e) => [
                'date' => $e->entry_date,
                'amount' => -$e->amount,
                'type' => 'expense',
                'source' => $e->description,
            ]);

        return $allocations->concat($extras)->concat($expenses)
            ->sortByDesc('date')
            ->values();
    }

    // Fija/actualiza la meta de ahorro de un sobre (null para quitarla).
    public function updateGoal(Request $request, $id)
    {
        $data = $request->validate([
            'goal_amount' => 'nullable|numeric|min:0',
        ]);

        $envelope = Envelope::findOrFail($id);
        $envelope->update(['goal_amount' => $data['goal_amount'] ?? null]);

        return $envelope;
    }
}
