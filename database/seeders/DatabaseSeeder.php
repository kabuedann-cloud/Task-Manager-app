<?php

namespace Database\Seeders;

use App\Enums\PriorityEnum;
use App\Enums\StatusEnum;
use App\Models\Task;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = [
            [
                'title' => 'Prepare API documentation',
                'due_date' => today()->toDateString(),
                'priority' => PriorityEnum::HIGH->value,
                'status' => StatusEnum::PENDING->value,
            ],
            [
                'title' => 'Review deployment checklist',
                'due_date' => today()->addDay()->toDateString(),
                'priority' => PriorityEnum::HIGH->value,
                'status' => StatusEnum::IN_PROGRESS->value,
            ],
            [
                'title' => 'Write feature tests',
                'due_date' => today()->addDays(2)->toDateString(),
                'priority' => PriorityEnum::MEDIUM->value,
                'status' => StatusEnum::DONE->value,
            ],
            [
                'title' => 'Clean up README examples',
                'due_date' => today()->addDays(3)->toDateString(),
                'priority' => PriorityEnum::LOW->value,
                'status' => StatusEnum::PENDING->value,
            ],
        ];

        foreach ($tasks as $task) {
            Task::updateOrCreate(
                [
                    'title' => $task['title'],
                    'due_date' => $task['due_date'],
                ],
                $task
            );
        }
    }
}
