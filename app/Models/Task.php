<?php

namespace App\Models;

use App\Enums\PriorityEnum;
use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'due_date',
        'priority',
        'status',
    ];

    protected $casts = [
        'priority' => PriorityEnum::class,
        'status' => StatusEnum::class,
        'due_date' => 'date',
    ];

    public function scopeOrderedForListing(Builder $query): Builder
    {
        return $query
            ->orderByRaw("
                CASE priority
                    WHEN 'high' THEN 1
                    WHEN 'medium' THEN 2
                    WHEN 'low' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('due_date')
            ->orderBy('id');
    }
}
