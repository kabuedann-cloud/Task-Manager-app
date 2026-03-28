<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Task;
use App\Actions\CreateTaskAction;
use App\Actions\UpdateTaskStatusAction;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Resources\TaskResource;
use App\Enums\StatusEnum;
use App\Http\Resources\TaskReportResource;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::query();

        if ($request->has('status') && $request->status !== 'All Status') {
            $query->where('status', strtolower(str_replace(' ', '_', $request->status)));
        }

        $tasks = $query->latest()->get();

        return Inertia::render('Tasks/Index', [
            'tasks' => TaskResource::collection($tasks),
        ]);
    }

    public function store(StoreTaskRequest $request, CreateTaskAction $createTaskAction)
    {
        $createTaskAction->execute($request->validated());

        return to_route('tasks.index');
    }

    public function updateStatus(Request $request, Task $task, UpdateTaskStatusAction $updateTaskStatusAction)
    {
        $status = StatusEnum::from($request->status);
        $updateTaskStatusAction->execute($task, $status);

        return to_route('tasks.index');
    }

    public function destroy(Task $task)
    {
        if ($task->status !== StatusEnum::DONE) {
            abort(403, 'Only completed tasks can be deleted.');
        }

        $task->delete();

        return to_route('tasks.index');
    }

    public function report(Request $request)
    {
        if ($request->wantsJson() || $request->is('api/*')) {
            $date = $request->input('date', today()->toDateString());

            $tasks = Task::whereDate('created_at', $date)
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
                'summary' => $summary
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
}
