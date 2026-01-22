<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Http\Resources\TodoResource;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TodoController extends Controller
{
    public function index(Request $request)
    {
        $query = Auth::user()->todos()->with('category');

        if ($request->filled('status')) {
            match ($request->status) {
                'pending' => $query->pending(),
                'completed' => $query->completed(),
                default => null,
            };
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->input('per_page', 15), 50);

        $todos = $query->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END")
            ->orderBy('due_date')
            ->paginate($perPage);

        $stats = [
            'total' => Auth::user()->todos()->count(),
            'completed' => Auth::user()->todos()->completed()->count(),
            'pending' => Auth::user()->todos()->pending()->count(),
            'overdue' => Auth::user()->todos()->overdue()->count(),
        ];

        return TodoResource::collection($todos)->additional([
            'stats' => $stats,
        ]);
    }

    public function store(StoreTodoRequest $request)
    {
        $todo = Auth::user()->todos()->create([
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority ?? 'medium',
            'due_date' => $request->due_date,
            'category_id' => $request->category_id,
        ]);

        $todo->load('category');

        return (new TodoResource($todo))
            ->additional(['message' => 'Tarefa criada com sucesso!'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Todo $todo)
    {
        $this->authorize('view', $todo);

        $todo->load('category');

        return new TodoResource($todo);
    }

    public function update(UpdateTodoRequest $request, Todo $todo)
    {
        $this->authorize('update', $todo);

        $todo->update([
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority ?? $todo->priority,
            'due_date' => $request->due_date,
            'category_id' => $request->category_id,
        ]);

        $todo->load('category');

        return (new TodoResource($todo))
            ->additional(['message' => 'Tarefa atualizada com sucesso!']);
    }

    public function toggle(Todo $todo)
    {
        $this->authorize('update', $todo);

        $todo->update([
            'completed' => !$todo->completed,
            'completed_at' => !$todo->completed ? now() : null,
        ]);

        $todo->load('category');

        $message = $todo->completed
            ? 'Tarefa marcada como concluída!'
            : 'Tarefa marcada como pendente!';

        return (new TodoResource($todo))
            ->additional(['message' => $message]);
    }

    public function destroy(Todo $todo)
    {
        $this->authorize('delete', $todo);

        $todo->delete();

        return response()->json([
            'message' => 'Tarefa movida para lixeira!',
        ]);
    }

    public function trashed()
    {
        $todos = Auth::user()->todos()
            ->onlyTrashed()
            ->with('category')
            ->orderBy('deleted_at', 'desc')
            ->paginate(15);

        return TodoResource::collection($todos);
    }

    public function restore(int $id)
    {
        $todo = Auth::user()->todos()->onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $todo);

        $todo->restore();
        $todo->load('category');

        return (new TodoResource($todo))
            ->additional(['message' => 'Tarefa restaurada com sucesso!']);
    }

    public function forceDelete(int $id)
    {
        $todo = Auth::user()->todos()->onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $todo);

        $todo->forceDelete();

        return response()->json([
            'message' => 'Tarefa excluída permanentemente!',
        ]);
    }
}
