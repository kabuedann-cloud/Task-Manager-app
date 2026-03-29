<?php

namespace App\Http\Controllers;

use App\Actions\CreateTaskAction;
use App\Actions\UpdateTaskStatusAction;
use App\Enums\StatusEnum;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Http\Resources\TaskReportResource;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        if ($this->isApiRequest($request)) {
            $validated = $request->validate([
                'status' => ['sometimes', Rule::enum(StatusEnum::class)],
            ]);

            $tasks = $this->applyTaskListingOrder(
                Task::query()->when(
                    isset($validated['status']),
                    fn (Builder $query) => $query->where('status', $validated['status'])
                )
            )->get();

            if ($tasks->isEmpty()) {
                return response()->json([
                    'message' => 'No tasks found.',
                    'data' => [],
                ]);
            }

            return TaskResource::collection($tasks);
        }

        $query = Task::query();

        if ($request->has('status') && $request->status !== 'All Status') {
            $query->where('status', strtolower(str_replace(' ', '_', $request->status)));
        }

        $tasks = $this->applyTaskListingOrder($query)->get();

        return Inertia::render('Tasks/Index', [
            'tasks' => TaskResource::collection($tasks),
        ]);
    }

    public function store(StoreTaskRequest $request, CreateTaskAction $createTaskAction)
    {
        $task = $createTaskAction->execute($request->validated());

        if ($this->isApiRequest($request)) {
            return (new TaskResource($task))
                ->response()
                ->setStatusCode(201);
        }

        return to_route('tasks.index');
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task, UpdateTaskStatusAction $updateTaskStatusAction)
    {
        $status = StatusEnum::from($request->validated('status'));
        $task = $updateTaskStatusAction->execute($task, $status);

        if ($this->isApiRequest($request)) {
            return new TaskResource($task);
        }

        return to_route('tasks.index');
    }

    public function destroy(Task $task)
    {
        if ($task->status !== StatusEnum::DONE) {
            abort(403, 'Only completed tasks can be deleted.');
        }

        $task->delete();

        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json(['message' => 'Task deleted successfully']);
        }

        return to_route('tasks.index');
    }

    public function report(Request $request)
    {
        if ($this->isApiRequest($request)) {
            $validated = $request->validate([
                'date' => ['nullable', 'date_format:Y-m-d'],
            ]);

            $date = $validated['date'] ?? today()->toDateString();

            $tasks = Task::whereDate('due_date', $date)
                ->select('priority', 'status', DB::raw('count(*) as count'))
                ->groupBy('priority', 'status')
                ->get();

            $summary = [
                'high' => ['pending' => 0, 'in_progress' => 0, 'done' => 0],
                'medium' => ['pending' => 0, 'in_progress' => 0, 'done' => 0],
                'low' => ['pending' => 0, 'in_progress' => 0, 'done' => 0],
            ];

            foreach ($tasks as $task) {
                $priority = $task->priority instanceof \BackedEnum ? $task->priority->value : $task->priority;
                $status = $task->status instanceof \BackedEnum ? $task->status->value : $task->status;

                if (isset($summary[$priority][$status])) {
                    $summary[$priority][$status] = (int) $task->count;
                }
            }

            return response()->json([
                'date' => $date,
                'summary' => $summary,
            ]);
        }

        $report = Task::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return Inertia::render('Tasks/Report', [
            'report' => TaskReportResource::collection($report),
            'total' => Task::count(),
        ]);
    }

    private function applyTaskListingOrder(Builder $query): Builder
    {
        return $query
            ->orderByRaw("
                CASE priority
                    WHEN 'high' THEN 1
                    WHEN 'medium' THEN 2
                    WHEN 'low' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('due_date')
            ->orderBy('id');
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->wantsJson() || $request->is('api/*');
    }
}
