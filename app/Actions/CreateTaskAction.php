<?php

namespace App\Actions;

use App\Models\Task;
use App\Enums\StatusEnum;
use Illuminate\Validation\ValidationException;

class CreateTaskAction
{
    public function execute(array $data): Task
    {
        // Business logic for creation (Status defaults to pending)
        $data['status'] = StatusEnum::PENDING;

        return Task::create($data);
    }
}
