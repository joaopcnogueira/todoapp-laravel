<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TimeEntryResource;
use App\Http\Resources\TodoResource;
use App\Models\TimeEntry;
use App\Models\Todo;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    public function start(Request $request, Todo $todo): JsonResponse
    {
        if ($todo->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        if ($todo->timer_started_at) {
            return response()->json([
                'message' => 'Timer já está rodando para esta tarefa.',
            ], 422);
        }

        $now = Carbon::now();

        $timeEntry = TimeEntry::create([
            'todo_id' => $todo->id,
            'user_id' => $request->user()->id,
            'started_at' => $now,
        ]);

        $todo->update(['timer_started_at' => $now]);

        return response()->json([
            'message' => 'Timer iniciado!',
            'time_entry' => new TimeEntryResource($timeEntry),
            'todo' => new TodoResource($todo->fresh()),
        ]);
    }

    public function stop(Request $request, Todo $todo): JsonResponse
    {
        if ($todo->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        if (!$todo->timer_started_at) {
            return response()->json([
                'message' => 'Nenhum timer ativo para esta tarefa.',
            ], 422);
        }

        $activeEntry = $todo->activeTimeEntry;

        if (!$activeEntry) {
            $todo->update(['timer_started_at' => null]);
            return response()->json([
                'message' => 'Nenhum timer ativo encontrado.',
            ], 422);
        }

        $now = Carbon::now();
        $durationSeconds = $now->diffInSeconds($activeEntry->started_at);

        $activeEntry->update([
            'ended_at' => $now,
            'duration_seconds' => $durationSeconds,
        ]);

        $todo->update([
            'timer_started_at' => null,
            'total_time_seconds' => $todo->total_time_seconds + $durationSeconds,
        ]);

        return response()->json([
            'message' => 'Timer parado!',
            'time_entry' => new TimeEntryResource($activeEntry->fresh()),
            'todo' => new TodoResource($todo->fresh()),
        ]);
    }

    public function status(Request $request, Todo $todo): JsonResponse
    {
        if ($todo->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $currentSessionSeconds = 0;
        if ($todo->timer_started_at) {
            $currentSessionSeconds = Carbon::now()->diffInSeconds($todo->timer_started_at);
        }

        return response()->json([
            'is_running' => $todo->is_timer_running,
            'timer_started_at' => $todo->timer_started_at?->toIso8601String(),
            'total_time_seconds' => $todo->total_time_seconds,
            'current_session_seconds' => $currentSessionSeconds,
            'formatted_total_time' => $todo->formatted_total_time,
        ]);
    }

    public function destroy(Request $request, Todo $todo, TimeEntry $timeEntry): JsonResponse
    {
        if ($todo->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        if ($timeEntry->todo_id !== $todo->id) {
            abort(404, 'Time entry não encontrada para esta tarefa.');
        }

        if ($timeEntry->isRunning()) {
            $todo->update(['timer_started_at' => null]);
        } else {
            $todo->update([
                'total_time_seconds' => max(0, $todo->total_time_seconds - ($timeEntry->duration_seconds ?? 0)),
            ]);
        }

        $timeEntry->delete();

        return response()->json([
            'message' => 'Entrada de tempo removida!',
            'todo' => new TodoResource($todo->fresh()),
        ]);
    }
}
