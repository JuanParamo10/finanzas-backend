<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixedExpense extends Model
{
    use HasFactory;

    protected $fillable = ['income_profile_id', 'name', 'amount', 'frequency'];

    public function incomeProfile(): BelongsTo
    {
        return $this->belongsTo(IncomeProfile::class);
    }
}
