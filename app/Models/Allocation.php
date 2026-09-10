<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Allocation extends Model
{
    use HasFactory;

    protected $fillable = ['income_entry_id', 'envelope_id', 'amount', 'type'];

    public function incomeEntry(): BelongsTo
    {
        return $this->belongsTo(IncomeEntry::class);
    }

    public function envelope(): BelongsTo
    {
        return $this->belongsTo(Envelope::class);
    }
}
