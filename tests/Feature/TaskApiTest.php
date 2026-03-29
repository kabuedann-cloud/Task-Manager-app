<?php

namespace Tests\Feature;

use App\Enums\PriorityEnum;
use App\Enums\StatusEnum;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_task_via_the_api(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Ship assignment',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'priority' => PriorityEnum::HIGH->value,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.title', 'Ship assignment')
            ->assertJsonPath('data.priority', PriorityEnum::HIGH->value)
            ->assertJsonPath('data.status', StatusEnum::PENDING->value);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Ship assignment',
            'priority' => PriorityEnum::HIGH->value,
            'status' => StatusEnum::PENDING->value,
        ]);
    }

    public function test_validates_required_fields_on_creation(): void
    {
        $this->postJson('/api/tasks', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'due_date', 'priority']);
    }

    public function test_rejects_duplicate_title_on_same_due_date(): void
    {
        Task::create([
            'title' => 'Meeting',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'priority' => PriorityEnum::HIGH->value,
            'status' => StatusEnum::PENDING->value,
        ]);

        $this->postJson('/api/tasks', [
            'title' => 'Meeting',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'priority' => PriorityEnum::LOW->value,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    }

    public function test_rejects_past_due_dates(): void
    {
        $this->postJson('/api/tasks', [
            'title' => 'Old Task',
            'due_date' => now()->subDay()->format('Y-m-d'),
            'priority' => PriorityEnum::MEDIUM->value,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['due_date']);
    }

    public function test_lists_tasks_sorted_by_priority_then_due_date(): void
    {
        Task::create([
            'title' => 'Medium first',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'priority' => PriorityEnum::MEDIUM->value,
            'status' => StatusEnum::PENDING->value,
        ]);

        Task::create([
            'title' => 'High later',
            'due_date' => now()->addDays(3)->format('Y-m-d'),
            'priority' => PriorityEnum::HIGH->value,
            'status' => StatusEnum::PENDING->value,
        ]);

        Task::create([
            'title' => 'High sooner',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'priority' => PriorityEnum::HIGH->value,
            'status' => StatusEnum::IN_PROGRESS->value,
        ]);

        Task::create([
            'title' => 'Low task',
            'due_date' => now()->format('Y-m-d'),
            'priority' => PriorityEnum::LOW->value,
            'status' => StatusEnum::DONE->value,
        ]);

        $this->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'High sooner')
            ->assertJsonPath('data.1.title', 'High later')
            ->assertJsonPath('data.2.title', 'Medium first')
            ->assertJsonPath('data.3.title', 'Low task');
    }

    public function test_can_filter_list_by_status(): void
    {
        Task::create([
            'title' => 'Pending task',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'priority' => PriorityEnum::HIGH->value,
            'status' => StatusEnum::PENDING->value,
        ]);

        Task::create([
            'title' => 'Done task',
            'due_date' => now()->addDays(2)->format('Y-m-d'),
            'priority' => PriorityEnum::LOW->value,
            'status' => StatusEnum::DONE->value,
        ]);

        $this->getJson('/api/tasks?status=done')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Done task');
    }

    public function test_returns_meaningful_json_when_no_tasks_exist(): void
    {
        $this->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonPath('message', 'No tasks found.')
            ->assertJsonPath('data', []);
    }

    public function test_enforces_status_progression_order(): void
    {
        $task = Task::create([
            'title' => 'Logic Task',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'priority' => PriorityEnum::HIGH->value,
            'status' => StatusEnum::PENDING->value,
        ]);

        $this->patchJson("/api/tasks/{$task->id}/status", ['status' => StatusEnum::DONE->value])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);

        $this->patchJson("/api/tasks/{$task->id}/status", ['status' => StatusEnum::IN_PROGRESS->value])
            ->assertOk()
            ->assertJsonPath('data.status', StatusEnum::IN_PROGRESS->value);
    }

    public function test_validates_the_requested_status_value(): void
    {
        $task = Task::create([
            'title' => 'Validation Task',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'priority' => PriorityEnum::MEDIUM->value,
            'status' => StatusEnum::PENDING->value,
        ]);

        $this->patchJson("/api/tasks/{$task->id}/status", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);

        $this->patchJson("/api/tasks/{$task->id}/status", ['status' => 'archived'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    public function test_cannot_delete_a_pending_or_in_progress_task(): void
    {
        $task = Task::create([
            'title' => 'Active Task',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'priority' => PriorityEnum::HIGH->value,
            'status' => StatusEnum::PENDING->value,
        ]);

        $this->deleteJson("/api/tasks/{$task->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_can_delete_a_done_task(): void
    {
        $task = Task::create([
            'title' => 'Done Task',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'priority' => PriorityEnum::HIGH->value,
            'status' => StatusEnum::DONE->value,
        ]);

        $this->deleteJson("/api/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Task deleted successfully');

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_daily_report_returns_counts_per_priority_and_status_for_the_due_date(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        Task::create([
            'title' => 'High pending',
            'due_date' => $date,
            'priority' => PriorityEnum::HIGH->value,
            'status' => StatusEnum::PENDING->value,
        ]);

        Task::create([
            'title' => 'Medium done',
            'due_date' => $date,
            'priority' => PriorityEnum::MEDIUM->value,
            'status' => StatusEnum::DONE->value,
        ]);

        Task::create([
            'title' => 'Other day task',
            'due_date' => now()->addDays(3)->format('Y-m-d'),
            'priority' => PriorityEnum::LOW->value,
            'status' => StatusEnum::DONE->value,
        ]);

        $this->getJson("/api/tasks/report?date={$date}")
            ->assertOk()
            ->assertJsonPath('date', $date)
            ->assertJsonPath('summary.high.pending', 1)
            ->assertJsonPath('summary.high.in_progress', 0)
            ->assertJsonPath('summary.high.done', 0)
            ->assertJsonPath('summary.medium.pending', 0)
            ->assertJsonPath('summary.medium.in_progress', 0)
            ->assertJsonPath('summary.medium.done', 1)
            ->assertJsonPath('summary.low.done', 0);
    }
}
