<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtraIncome extends Model
{
    use HasFactory;

    protected $fillable = ['envelope_id', 'amount', 'description', 'entry_date'];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function envelope(): BelongsTo
    {
        return $this->belongsTo(Envelope::class);
    }
}
