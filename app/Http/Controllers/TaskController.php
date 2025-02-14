<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        return Task::orderBy('id', 'desc')->get();
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|string|in:todo,in_progress,completed',
            ]);

            $task = Task::create($validated);

            return response()->json($task, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao criar a tarefa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        return Task::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|max:255',
            'description' => 'sometimes|string',
            'completed' => 'boolean',
            'status' => 'sometimes|string|in:todo,in_progress,completed',
        ]);

        $task = Task::findOrFail($id);
        if ($request->has('title')) {
            $task->title = $request->title;
        }
        if ($request->has('description')) {
            $task->description = $request->description;
        }
        if ($request->has('completed')) {
            $task->completed = $request->completed;
        }
        if ($request->has('status')) {
            $task->status = $request->status;
        }
        $task->save();

        return $task;
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();
        return response(null, 204);
    }
}