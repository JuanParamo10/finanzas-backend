<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AllocationRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AllocationRuleController extends Controller
{
    private const RULE_SETS = ['sueldo', 'indrive', 'extra'];

    // GET /allocation-rules?rule_set=sueldo|indrive|extra
    public function index(Request $request)
    {
        $data = $request->validate([
            'rule_set' => ['required', Rule::in(self::RULE_SETS)],
        ]);

        return AllocationRule::where('rule_set', $data['rule_set'])
            ->with('envelope')
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($rule) => [
                'envelope_id' => $rule->envelope_id,
                'name' => $rule->envelope->name,
                'percentage' => $rule->percentage,
            ]);
    }

    // PUT /allocation-rules — reemplaza todas las reglas de un rule_set.
    // El orden del array define quién absorbe el residuo de redondeo (el último).
    public function update(Request $request)
    {
        $data = $request->validate([
            'rule_set' => ['required', Rule::in(self::RULE_SETS)],
            'rules' => ['required', 'array', 'min:1'],
            'rules.*.envelope_id' => ['required', 'integer', 'exists:envelopes,id'],
            'rules.*.percentage' => ['required', 'numeric', 'min:0', 'max:1'],
        ]);

        $envelopeIds = collect($data['rules'])->pluck('envelope_id');
        if ($envelopeIds->unique()->count() !== $envelopeIds->count()) {
            throw ValidationException::withMessages(['rules' => 'No puedes repetir el mismo sobre dos veces en el reparto.']);
        }

        $total = collect($data['rules'])->sum('percentage');
        if (abs($total - 1.0) > 0.005) {
            throw ValidationException::withMessages(['rules' => 'Los porcentajes deben sumar 100%. Ahora suman '.round($total * 100, 1).'%.']);
        }

        DB::transaction(function () use ($data) {
            AllocationRule::where('rule_set', $data['rule_set'])->delete();

            foreach ($data['rules'] as $order => $rule) {
                AllocationRule::create([
                    'rule_set' => $data['rule_set'],
                    'envelope_id' => $rule['envelope_id'],
                    'percentage' => $rule['percentage'],
                    'sort_order' => $order,
                ]);
            }
        });

        return $this->index(new Request(['rule_set' => $data['rule_set']]));
    }
}
