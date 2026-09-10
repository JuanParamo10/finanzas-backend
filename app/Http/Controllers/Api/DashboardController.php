<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExtraIncome;
use App\Models\IncomeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class DashboardController extends Controller
{
    // Informe general para la pantalla de Resumen, con 4 vistas: hoy, esta
    // semana, este mes, y global (todo el historial) — ?period=today|week|month|all.
    public function summary(Request $request)
    {
        $period = $request->query('period', 'month');
        $startDate = $this->startDateFor($period);

        $incomeQuery = IncomeEntry::query();
        $extraQuery = ExtraIncome::query();
        $expenseQuery = Expense::query();

        if ($startDate) {
            $incomeQuery->where('entry_date', '>=', $startDate);
            $extraQuery->where('entry_date', '>=', $startDate);
            $expenseQuery->where('entry_date', '>=', $startDate);
        }

        $incomeTotal = (clone $incomeQuery)->sum('gross_amount');
        $extraTotal = (clone $extraQuery)->sum('amount');
        $gasTotal = (clone $incomeQuery)->sum('gas_expense');
        $appFeeTotal = (clone $incomeQuery)->sum('app_fee');
        $expensesTotal = (clone $expenseQuery)->sum('amount');

        $recentEntries = (clone $incomeQuery)
            ->with('incomeProfile')
            ->latest('entry_date')
            ->take(15)
            ->get()
            ->map(fn ($e) => [
                'date' => $e->entry_date,
                'label' => $e->incomeProfile->name,
                'amount' => $e->gross_amount,
                'type' => 'income',
            ]);

        // Un ingreso extra repartido automáticamente crea varias filas (una por
        // sobre) con la misma fecha y descripción; se agrupan en un solo
        // movimiento para el feed, en vez de mostrarlo repetido 4 veces.
        $recentExtras = (clone $extraQuery)
            ->selectRaw('entry_date, description, SUM(amount) as amount')
            ->groupBy('entry_date', 'description')
            ->orderByDesc('entry_date')
            ->take(15)
            ->get()
            ->map(fn ($e) => [
                'date' => $e->entry_date,
                'label' => $e->description ?: 'Ingreso extra',
                'amount' => $e->amount,
                'type' => 'extra',
            ]);

        $recentExpenses = (clone $expenseQuery)
            ->with('envelope')
            ->latest('entry_date')
            ->take(15)
            ->get()
            ->map(fn ($e) => [
                'date' => $e->entry_date,
                'label' => $e->description,
                'amount' => -$e->amount,
                'type' => 'expense',
            ]);

        $activity = $recentEntries->concat($recentExtras)->concat($recentExpenses)
            ->sortByDesc('date')
            ->take(15)
            ->values();

        return response()->json([
            'period' => $period,
            'income_total' => (float) $incomeTotal + (float) $extraTotal,
            'gas_total' => (float) $gasTotal,
            'app_fee_total' => (float) $appFeeTotal,
            'expenses_total' => (float) $expensesTotal,
            'activity' => $activity,
        ]);
    }

    private function startDateFor(string $period): ?string
    {
        return match ($period) {
            'today' => Date::now()->startOfDay()->toDateString(),
            'week' => Date::now()->startOfWeek()->toDateString(),
            'month' => Date::now()->startOfMonth()->toDateString(),
            default => null, // 'all'
        };
    }
}
