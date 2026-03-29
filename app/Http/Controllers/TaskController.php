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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $tasks = Task::query()
            ->when(
                $request->filled('status') && $request->status !== 'All Status',
                fn ($query) => $query->where('status', strtolower(str_replace(' ', '_', $request->status)))
            )
            ->orderedForListing()
            ->get();

        return Inertia::render('Tasks/Index', ['tasks' => TaskResource::collection($tasks)]);
    }

    public function store(StoreTaskRequest $request, CreateTaskAction $createTaskAction)
    {
        $createTaskAction->execute($request->validated());

        return to_route('tasks.index');
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task, UpdateTaskStatusAction $updateTaskStatusAction)
    {
        $updateTaskStatusAction->execute($task, StatusEnum::from($request->validated('status')));

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

    public function report()
    {
        $report = Task::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return Inertia::render('Tasks/Report', [
            'report' => TaskReportResource::collection($report),
            'total' => Task::count(),
        ]);
    }
}
