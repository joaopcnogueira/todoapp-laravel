<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Tarefa
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('todos.update', $todo) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Titulo *</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $todo->title) }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('title') border-red-500 @enderror">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descricao</label>
                        <textarea name="description" id="description" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-500 @enderror">{{ old('description', $todo->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Prioridade</label>
                            <select name="priority" id="priority" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($priorities as $priority)
                                    <option value="{{ $priority->value }}" {{ old('priority', $todo->priority?->value) == $priority->value ? 'selected' : '' }}>{{ $priority->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                            <select name="category_id" id="category_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Sem categoria</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $todo->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">Data de Vencimento</label>
                            <input type="date" name="due_date" id="due_date" value="{{ old('due_date', $todo->due_date?->format('Y-m-d')) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('due_date') border-red-500 @enderror">
                            @error('due_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-4">
                        <a href="{{ route('todos.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Salvar Alteracoes</button>
                    </div>
                </form>
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
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acoes</th>
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
                                        <td class="px-4 py-3 text-right">
                                            <button x-on:click="deleteEntry(entry.id)" class="text-red-600 hover:text-red-800" title="Excluir">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
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
