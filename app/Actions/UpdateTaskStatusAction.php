<?php

namespace App\Actions;

use App\Models\Task;
use App\Enums\StatusEnum;
use Illuminate\Validation\ValidationException;

class UpdateTaskStatusAction
{
    public function execute(Task $task, StatusEnum $newStatus): Task
    {
        if (!$task->status->canTransitionTo($newStatus)) {
            throw ValidationException::withMessages([
                'status' => "Cannot transition task from {$task->status->value} to {$newStatus->value}.",
            ]);
        }

        $task->update(['status' => $newStatus]);

        return $task;
    }
}
