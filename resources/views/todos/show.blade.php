<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalhes da Tarefa
            </h2>
            <a href="{{ route('todos.edit', $todo) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Editar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <!-- Task Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center gap-3 mb-4">
                    <h3 class="text-2xl font-semibold text-gray-900 {{ $todo->completed ? 'line-through text-gray-400' : '' }}">{{ $todo->title }}</h3>
                    @if($todo->completed)
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Concluida</span>
                    @endif
                </div>

                @if($todo->description)
                    <p class="text-gray-600 mb-6">{{ $todo->description }}</p>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Prioridade:</span>
                        @if($todo->priority)
                            <span class="ml-2 px-2 py-1 text-xs rounded-full
                                {{ $todo->priority->value == 'high' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $todo->priority->value == 'medium' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $todo->priority->value == 'low' ? 'bg-green-100 text-green-800' : '' }}
                            ">{{ $todo->priority->label() }}</span>
                        @else
                            <span class="ml-2 text-gray-400">Nenhuma</span>
                        @endif
                    </div>

                    <div>
                        <span class="text-gray-500">Categoria:</span>
                        @if($todo->category)
                            <span class="ml-2 px-2 py-1 text-xs rounded-full text-white" style="background-color: {{ $todo->category->color }}">{{ $todo->category->name }}</span>
                        @else
                            <span class="ml-2 text-gray-400">Nenhuma</span>
                        @endif
                    </div>

                    <div>
                        <span class="text-gray-500">Vencimento:</span>
                        @if($todo->due_date)
                            <span class="ml-2 {{ $todo->is_overdue ? 'text-red-600 font-medium' : 'text-gray-900' }}">{{ $todo->due_date->format('d/m/Y') }}</span>
                        @else
                            <span class="ml-2 text-gray-400">Nenhum</span>
                        @endif
                    </div>
                </div>

                @if($todo->completed_at)
                    <div class="mt-4 text-sm text-green-600">
                        Concluida em {{ $todo->completed_at->format('d/m/Y H:i') }}
                    </div>
                @endif

                <div class="mt-6 flex justify-between">
                    <a href="{{ route('todos.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Voltar</a>
                    <form action="{{ route('todos.destroy', $todo) }}" method="POST" onsubmit="return confirm('Mover tarefa para lixeira?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Excluir</button>
                    </form>
                </div>
            </div>

            <!-- Time Tracking Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mt-6"
                x-data="timeEntries({{ Js::from([
                    'todoId' => $todo->id,
                    'entries' => $todo->timeEntries->map(fn($e) => [
                        'id' => $e->id,
                        'started_at' => $e->started_at->format('d/m/Y H:i:s'),
                        'ended_at' => $e->ended_at?->format('d/m/Y H:i:s'),
                        'formatted_duration' => $e->formatted_duration,
                        'is_running' => $e->isRunning(),
                    ]),
                ]) }})"
                x-on:timer-stopped.window="handleTimerStopped($event)">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Registro de Tempo</h3>

                @if(!$todo->completed)
                    <!-- Large Timer Control -->
                    <div x-data="timer({{ Js::from([
                        'todoId' => $todo->id,
                        'isRunning' => $todo->is_timer_running,
                        'startedAt' => $todo->timer_started_at?->toIso8601String(),
                        'totalSeconds' => $todo->total_time_seconds ?? 0,
                    ]) }})" class="flex items-center justify-center gap-6 p-6 bg-gray-50 rounded-lg mb-6">
                        <span x-text="displayTime" class="text-4xl font-mono" :class="isRunning ? 'text-green-600' : 'text-gray-700'"></span>
                        <button x-on:click="toggleTimer()" :disabled="isLoading" class="px-6 py-3 rounded-lg font-semibold transition-colors" :class="isRunning ? 'bg-orange-500 text-white hover:bg-orange-600' : 'bg-green-500 text-white hover:bg-green-600'">
                            <span x-show="!isRunning">Iniciar</span>
                            <span x-show="isRunning">Parar</span>
                        </button>
                    </div>
                @else
                    <div class="flex items-center justify-center gap-4 p-6 bg-gray-50 rounded-lg mb-6">
                        <span class="text-gray-500">Tempo total:</span>
                        <span class="text-2xl font-mono text-gray-700">{{ $todo->formatted_total_time }}</span>
                    </div>
                @endif

                <!-- Time Entries History -->
                <h4 class="text-sm font-medium text-gray-700 mb-3">Historico de Sessoes</h4>
                <template x-if="entries.length === 0">
                    <p class="text-gray-500 text-sm">Nenhuma sessao registrada.</p>
                </template>
                <template x-if="entries.length > 0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inicio</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fim</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duracao</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="entry in entries" :key="entry.id">
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900" x-text="entry.started_at"></td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            <span x-show="entry.ended_at" x-text="entry.ended_at"></span>
                                            <span x-show="!entry.ended_at" class="text-green-600 font-medium">Em andamento...</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-mono" x-text="entry.formatted_duration" :class="entry.is_running ? 'text-green-600' : 'text-gray-900'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-app-layout>
