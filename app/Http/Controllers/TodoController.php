<?php

namespace App\Http\Controllers;

use App\Enums\Priority;
use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
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

        $todos = $query->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END")
            ->orderBy('due_date')
            ->paginate(15);

        $stats = [
            'total' => Auth::user()->todos()->count(),
            'completed' => Auth::user()->todos()->completed()->count(),
            'pending' => Auth::user()->todos()->pending()->count(),
            'overdue' => Auth::user()->todos()->overdue()->count(),
        ];

        $categories = Auth::user()->categories()->orderBy('name')->get();

        return view('todos.index', compact('todos', 'stats', 'categories'));
    }

    public function create()
    {
        $categories = Auth::user()->categories()->orderBy('name')->get();
        $priorities = Priority::cases();

        return view('todos.create', compact('categories', 'priorities'));
    }

    public function store(StoreTodoRequest $request)
    {
        Auth::user()->todos()->create([
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority ?? Priority::MEDIUM,
            'due_date' => $request->due_date,
            'category_id' => $request->category_id,
        ]);

        return redirect()->route('todos.index')
            ->with('success', 'Tarefa criada com sucesso!');
    }

    public function edit(Todo $todo)
    {
        $this->authorize('update', $todo);

        $categories = Auth::user()->categories()->orderBy('name')->get();
        $priorities = Priority::cases();

        return view('todos.edit', compact('todo', 'categories', 'priorities'));
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

        return redirect()->route('todos.index')
            ->with('success', 'Tarefa atualizada com sucesso!');
    }

    public function toggle(Todo $todo)
    {
        $this->authorize('update', $todo);

        $todo->update([
            'completed' => !$todo->completed,
            'completed_at' => !$todo->completed ? now() : null,
        ]);

        $message = $todo->completed
            ? 'Tarefa marcada como concluída!'
            : 'Tarefa marcada como pendente!';

        return redirect()->back()->with('success', $message);
    }

    public function destroy(Todo $todo)
    {
        $this->authorize('delete', $todo);

        $todo->delete();

        return redirect()->route('todos.index')
            ->with('success', 'Tarefa movida para lixeira!');
    }

    public function trashed()
    {
        $todos = Auth::user()->todos()
            ->onlyTrashed()
            ->with('category')
            ->orderBy('deleted_at', 'desc')
            ->paginate(15);

        return view('todos.trashed', compact('todos'));
    }

    public function restore(int $id)
    {
        $todo = Auth::user()->todos()->onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $todo);

        $todo->restore();

        return redirect()->route('todos.trashed')
            ->with('success', 'Tarefa restaurada com sucesso!');
    }

    public function forceDelete(int $id)
    {
        $todo = Auth::user()->todos()->onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $todo);

        $todo->forceDelete();

        return redirect()->route('todos.trashed')
            ->with('success', 'Tarefa excluída permanentemente!');
    }
}
