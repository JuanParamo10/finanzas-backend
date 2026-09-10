<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Envelope extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'icon', 'goal_amount'];

    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class);
    }

    public function extraIncomes(): HasMany
    {
        return $this->hasMany(ExtraIncome::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
