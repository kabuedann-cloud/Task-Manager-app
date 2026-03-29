<?php

namespace App\Actions;

use App\Enums\StatusEnum;
use App\Models\Task;

class CreateTaskAction
{
    public function execute(array $data): Task
    {
        $data['status'] = StatusEnum::PENDING;

        return Task::create($data);
    }
}
