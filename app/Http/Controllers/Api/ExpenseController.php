<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    // Registra plata que sale de un sobre. La descripción (el "por qué") es
    // obligatoria a propósito: es lo que después vas a ver en el historial.
    public function store(Request $request)
    {
        $data = $request->validate([
            'envelope_id' => 'required|exists:envelopes,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'entry_date' => 'required|date',
        ]);

        return Expense::create($data)->load('envelope');
    }
}
