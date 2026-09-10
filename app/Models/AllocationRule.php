<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllocationRule extends Model
{
    use HasFactory;

    protected $fillable = ['rule_set', 'envelope_id', 'percentage', 'sort_order'];

    protected $casts = [
        'percentage' => 'float',
    ];

    public function envelope(): BelongsTo
    {
        return $this->belongsTo(Envelope::class);
    }
}
