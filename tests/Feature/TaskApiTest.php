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

    public function it_can_list_tasks()
    {
        // Simple list check
        Task::factory()->count(3)->create();
        $this->get('/')->assertStatus(200);
    }

    public function it_validates_required_fields_for_task_creation()
    {
        // Mandatory fields check
        $this->post('/tasks', [])->assertSessionHasErrors(['title', 'due_date', 'priority']);
    }

    public function it_enforces_unique_title_and_due_date_constraint()
    {
        $task = Task::create([
            'title' => 'Meeting',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'priority' => PriorityEnum::HIGH->value,
            'status' => StatusEnum::PENDING->value,
        ]);

        // Duplicate title on same day
        $this->post('/tasks', [
            'title' => 'Meeting',
            'due_date' => $task->due_date,
            'priority' => PriorityEnum::LOW->value,
        ])->assertSessionHasErrors(['title']);
    }

    public function it_restricts_past_due_dates()
    {
        $this->post('/tasks', [
            'title' => 'Old Task',
            'due_date' => now()->subDay()->format('Y-m-d'),
            'priority' => PriorityEnum::MEDIUM->value,
        ])->assertSessionHasErrors(['due_date']);
    }

    public function it_enforces_status_transition_rules()
    {
        $task = Task::create([
            'title' => 'Logic Task',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'priority' => PriorityEnum::HIGH->value,
            'status' => StatusEnum::PENDING->value,
        ]);

        // Invalid jump
        $this->patch("/tasks/{$task->id}/status", ['status' => StatusEnum::DONE->value])
            ->assertSessionHasErrors(['status']);

        // Correct jump
        $this->patch("/tasks/{$task->id}/status", ['status' => StatusEnum::IN_PROGRESS->value])
            ->assertSessionHasNoErrors();
    }

    public function it_prevents_deletion_of_done_tasks()
    {
        $task = Task::create([
            'title' => 'Done Task',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'priority' => PriorityEnum::HIGH->value,
            'status' => StatusEnum::DONE->value,
        ]);

        $this->delete("/tasks/{$task->id}")->assertSessionHasErrors(['status']);
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function it_can_generate_a_daily_report()
    {
        Task::create(['title' => 'T1', 'due_date' => now()->format('Y-m-d'), 'priority' => 'low', 'status' => 'pending']);
        $this->get('/report')->assertStatus(200);
    }
}
