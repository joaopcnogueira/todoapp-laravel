<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Minhas Tarefas
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('categories.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Categorias
                </a>
                <a href="{{ route('todos.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    + Nova Tarefa
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Total</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Pendentes</div>
                    <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Concluidas</div>
                    <div class="text-3xl font-bold text-green-600">{{ $stats['completed'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Atrasadas</div>
                    <div class="text-3xl font-bold text-red-600">{{ $stats['overdue'] }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <form method="GET" action="{{ route('todos.index') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar tarefas..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todos</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendentes</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Concluidas</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prioridade</label>
                        <select name="priority" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todas</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Alta</option>
                            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Media</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Baixa</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                        <select name="category_id" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todas</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Filtrar</button>
                        <a href="{{ route('todos.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Limpar</a>
                    </div>
                </form>
            </div>

            <!-- Todos List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @forelse($todos as $todo)
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between hover:bg-gray-50 {{ $todo->completed ? 'bg-gray-50' : '' }}">
                        <div class="flex items-center gap-4 flex-1">
                            <form action="{{ route('todos.toggle', $todo) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-6 h-6 rounded-full border-2 flex items-center justify-center {{ $todo->completed ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300 hover:border-indigo-500' }}">
                                    @if($todo->completed)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @endif
                                </button>
                            </form>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="{{ $todo->completed ? 'line-through text-gray-400' : 'text-gray-900' }} font-medium">{{ $todo->title }}</span>
                                    @if($todo->priority)
                                        <span class="px-2 py-1 text-xs rounded-full
                                            {{ $todo->priority->value == 'high' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $todo->priority->value == 'medium' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $todo->priority->value == 'low' ? 'bg-green-100 text-green-800' : '' }}
                                        ">{{ $todo->priority->label() }}</span>
                                    @endif
                                    @if($todo->category)
                                        <span class="px-2 py-1 text-xs rounded-full text-white" style="background-color: {{ $todo->category->color }}">{{ $todo->category->name }}</span>
                                    @endif
                                </div>
                                @if($todo->description)
                                    <p class="text-sm text-gray-500 mt-1">{{ Str::limit($todo->description, 100) }}</p>
                                @endif
                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                                    @if($todo->due_date)
                                        <span class="{{ $todo->is_overdue ? 'text-red-500 font-medium' : '' }}">
                                            @if($todo->is_overdue)
                                                Atrasada - {{ $todo->due_date->format('d/m/Y') }}
                                            @else
                                                Vencimento: {{ $todo->due_date->format('d/m/Y') }}
                                            @endif
                                        </span>
                                    @endif
                                    @if($todo->completed_at)
                                        <span class="text-green-500">Concluida em {{ $todo->completed_at->format('d/m/Y') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <!-- Timer -->
                            @if(!$todo->completed)
                                <div x-data="timer({{ Js::from([
                                    'todoId' => $todo->id,
                                    'isRunning' => $todo->is_timer_running,
                                    'startedAt' => $todo->timer_started_at?->toIso8601String(),
                                    'totalSeconds' => $todo->total_time_seconds ?? 0,
                                ]) }})" class="flex items-center gap-2">
                                    <span x-text="displayTime" class="font-mono text-sm" :class="isRunning ? 'text-green-600 font-semibold' : 'text-gray-500'"></span>
                                    <button @click="toggleTimer()" :disabled="isLoading" class="w-8 h-8 rounded-full flex items-center justify-center transition-colors" :class="isRunning ? 'bg-orange-100 text-orange-600 hover:bg-orange-200' : 'bg-green-100 text-green-600 hover:bg-green-200'">
                                        <template x-if="!isRunning">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z"/>
                                            </svg>
                                        </template>
                                        <template x-if="isRunning">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                                            </svg>
                                        </template>
                                    </button>
                                </div>
                            @else
                                @if($todo->total_time_seconds > 0)
                                    <span class="font-mono text-sm text-gray-400">{{ $todo->formatted_total_time }}</span>
                                @endif
                            @endif

                            <a href="{{ route('todos.show', $todo) }}" class="text-gray-600 hover:text-gray-900">Ver</a>
                            <a href="{{ route('todos.edit', $todo) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                            <form action="{{ route('todos.destroy', $todo) }}" method="POST" onsubmit="return confirm('Mover tarefa para lixeira?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Excluir</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-gray-500">
                        Nenhuma tarefa encontrada. <a href="{{ route('todos.create') }}" class="text-indigo-600 hover:underline">Criar uma nova tarefa</a>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $todos->withQueryString()->links() }}
            </div>

            <!-- Trash Link -->
            <div class="mt-6 text-center">
                <a href="{{ route('todos.trashed') }}" class="text-gray-500 hover:text-gray-700">Ver lixeira</a>
            </div>
        </div>
    </div>
</x-app-layout>
