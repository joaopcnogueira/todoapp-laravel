<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use App\Models\Todo;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    public function start(Request $request, Todo $todo): JsonResponse
    {
        $this->authorize('update', $todo);

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
            'time_entry' => [
                'id' => $timeEntry->id,
                'started_at' => $timeEntry->started_at->toIso8601String(),
            ],
            'todo' => [
                'id' => $todo->id,
                'timer_started_at' => $todo->fresh()->timer_started_at->toIso8601String(),
                'total_time_seconds' => $todo->total_time_seconds,
            ],
        ]);
    }

    public function stop(Request $request, Todo $todo): JsonResponse
    {
        $this->authorize('update', $todo);

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
        $durationSeconds = (int) abs($now->diffInSeconds($activeEntry->started_at));

        $activeEntry->update([
            'ended_at' => $now,
            'duration_seconds' => $durationSeconds,
        ]);

        $todo->update([
            'timer_started_at' => null,
            'total_time_seconds' => ($todo->total_time_seconds ?? 0) + $durationSeconds,
        ]);

        $todo->refresh();

        return response()->json([
            'message' => 'Timer parado!',
            'time_entry' => [
                'id' => $activeEntry->id,
                'started_at' => $activeEntry->started_at->toIso8601String(),
                'ended_at' => $activeEntry->fresh()->ended_at->toIso8601String(),
                'duration_seconds' => $durationSeconds,
                'formatted_duration' => $activeEntry->fresh()->formatted_duration,
            ],
            'todo' => [
                'id' => $todo->id,
                'timer_started_at' => null,
                'total_time_seconds' => $todo->total_time_seconds,
                'formatted_total_time' => $todo->formatted_total_time,
            ],
        ]);
    }

    public function destroy(Request $request, Todo $todo, TimeEntry $timeEntry): JsonResponse
    {
        $this->authorize('update', $todo);

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
            'todo' => [
                'id' => $todo->fresh()->id,
                'total_time_seconds' => $todo->fresh()->total_time_seconds,
                'formatted_total_time' => $todo->fresh()->formatted_total_time,
            ],
        ]);
    }
}
