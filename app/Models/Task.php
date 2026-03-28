<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Enums\PriorityEnum;
use App\Enums\StatusEnum;

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
}
