<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\IncomeAllocationService;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function sueldo(Request $request, IncomeAllocationService $service)
    {
        $data = $request->validate([
            'gross_amount' => 'required|numeric|min:0',
            'entry_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        return $service->registerSueldo($data['gross_amount'], $data['entry_date'], $data['description'] ?? null);
    }

    public function indrive(Request $request, IncomeAllocationService $service)
    {
        $data = $request->validate([
            'gross_amount' => 'required|numeric|min:0',
            'gas_expense' => 'required|numeric|min:0',
            'app_fee' => 'required|numeric|min:0',
            'entry_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        return $service->registerIndrive($data['gross_amount'], $data['gas_expense'], $data['app_fee'], $data['entry_date'], $data['description'] ?? null);
    }

    public function extra(Request $request, IncomeAllocationService $service)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0',
            'envelope_id' => 'nullable|exists:envelopes,id',
            'entry_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        // Sin envelope_id: reparto automático entre varios sobres (IncomeAllocationService::EXTRA_RULES).
        if (empty($data['envelope_id'])) {
            return response()->json([
                'mode' => 'split',
                'allocations' => $service->registerExtraSplit($data['amount'], $data['entry_date'], $data['description'] ?? null),
            ]);
        }

        return response()->json([
            'mode' => 'single',
            'allocations' => [$service->registerExtra($data['amount'], $data['envelope_id'], $data['entry_date'], $data['description'] ?? null)->load('envelope')],
        ]);
    }
}
