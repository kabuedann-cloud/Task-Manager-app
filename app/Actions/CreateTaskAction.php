<?php

namespace App\Actions;

use App\Models\Task;
use App\Enums\StatusEnum;
use Illuminate\Validation\ValidationException;

class CreateTaskAction
{
    public function execute(array $data): Task
    {
        $data['status'] = StatusEnum::PENDING;

        return Task::create($data);
    }
}
