<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Enums\PriorityEnum;
use App\Enums\StatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_tasks()
    {
        Task::factory()->count(3)->create();
        $this->get('/')->assertStatus(200);
    }

    public function test_validates_required_fields_on_creation()
    {
        $this->post('/tasks', [])->assertSessionHasErrors(['title', 'due_date', 'priority']);
    }

    public function test_rejects_duplicate_title_on_same_due_date()
    {
        $task = Task::create([
            'title' => 'Meeting',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'priority' => PriorityEnum::HIGH->value,
            'status' => StatusEnum::PENDING->value,
        ]);

        $this->post('/tasks', [
            'title' => 'Meeting',
            'due_date' => $task->due_date,
            'priority' => PriorityEnum::LOW->value,
        ])->assertSessionHasErrors(['title']);
    }

    public function test_rejects_past_due_dates()
    {
        $this->post('/tasks', [
            'title' => 'Old Task',
            'due_date' => now()->subDay()->format('Y-m-d'),
            'priority' => PriorityEnum::MEDIUM->value,
        ])->assertSessionHasErrors(['due_date']);
    }

    public function test_enforces_status_progression_order()
    {
        $task = Task::create([
            'title' => 'Logic Task',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'priority' => PriorityEnum::HIGH->value,
            'status' => StatusEnum::PENDING->value,
        ]);

        $this->patch("/tasks/{$task->id}/status", ['status' => StatusEnum::DONE->value])
            ->assertSessionHasErrors(['status']);

        $this->patch("/tasks/{$task->id}/status", ['status' => StatusEnum::IN_PROGRESS->value])
            ->assertSessionHasNoErrors();
    }

    public function test_cannot_delete_a_pending_or_in_progress_task()
    {
        $task = Task::create([
            'title' => 'Active Task',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'priority' => PriorityEnum::HIGH->value,
            'status' => StatusEnum::PENDING->value,
        ]);

        $this->delete("/tasks/{$task->id}")->assertStatus(403);
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_can_delete_a_done_task()
    {
        $task = Task::create([
            'title' => 'Done Task',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'priority' => PriorityEnum::HIGH->value,
            'status' => StatusEnum::DONE->value,
        ]);

        $this->delete("/tasks/{$task->id}")->assertRedirect('/');
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_daily_report_returns_ok()
    {
        Task::create(['title' => 'T1', 'due_date' => now()->format('Y-m-d'), 'priority' => 'low', 'status' => 'pending']);
        $this->get('/tasks/report')->assertStatus(200);
    }
}
