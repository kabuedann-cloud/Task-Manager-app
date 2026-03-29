<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateTaskAction;
use App\Actions\UpdateTaskStatusAction;
use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['sometimes', Rule::enum(StatusEnum::class)],
        ]);

        $tasks = Task::query()
            ->when(
                isset($validated['status']),
                fn ($query) => $query->where('status', $validated['status'])
            )
            ->orderedForListing()
            ->get();

        if ($tasks->isEmpty()) {
            return response()->json([
                'message' => 'No tasks found.',
                'data' => [],
            ]);
        }

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request, CreateTaskAction $createTaskAction)
    {
        $task = $createTaskAction->execute($request->validated());

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    public function updateStatus(
        UpdateTaskStatusRequest $request,
        Task $task,
        UpdateTaskStatusAction $updateTaskStatusAction
    ) {
        $task = $updateTaskStatusAction->execute(
            $task,
            StatusEnum::from($request->validated('status'))
        );

        return new TaskResource($task);
    }

    public function destroy(Task $task)
    {
        if ($task->status !== StatusEnum::DONE) {
            abort(403, 'Only completed tasks can be deleted.');
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }

    public function report(Request $request)
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $date = $validated['date'] ?? today()->toDateString();

        $rows = Task::query()
            ->whereDate('due_date', $date)
            ->select('priority', 'status', DB::raw('count(*) as count'))
            ->groupBy('priority', 'status')
            ->get();

        $summary = [
            'high' => ['pending' => 0, 'in_progress' => 0, 'done' => 0],
            'medium' => ['pending' => 0, 'in_progress' => 0, 'done' => 0],
            'low' => ['pending' => 0, 'in_progress' => 0, 'done' => 0],
        ];

        foreach ($rows as $row) {
            $priority = $row->priority instanceof \BackedEnum ? $row->priority->value : $row->priority;
            $status = $row->status instanceof \BackedEnum ? $row->status->value : $row->status;

            if (isset($summary[$priority][$status])) {
                $summary[$priority][$status] = (int) $row->count;
            }
        }

        return response()->json([
            'date' => $date,
            'summary' => $summary,
        ]);
    }
}
