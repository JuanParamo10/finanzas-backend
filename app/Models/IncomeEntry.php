<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomeEntry extends Model
{
    use HasFactory;

    protected $fillable = ['income_profile_id', 'gross_amount', 'gas_expense', 'app_fee', 'entry_date', 'description'];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function incomeProfile(): BelongsTo
    {
        return $this->belongsTo(IncomeProfile::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class);
    }
}
