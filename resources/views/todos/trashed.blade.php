<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Lixeira
            </h2>
            <a href="{{ route('todos.index') }}" class="text-indigo-600 hover:text-indigo-900">Voltar para tarefas</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @forelse($todos as $todo)
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between hover:bg-gray-50">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-400 line-through">{{ $todo->title }}</span>
                                @if($todo->category)
                                    <span class="px-2 py-1 text-xs rounded-full text-white" style="background-color: {{ $todo->category->color }}">{{ $todo->category->name }}</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-400 mt-1">Excluida em {{ $todo->deleted_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <form action="{{ route('todos.restore', $todo->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-indigo-600 hover:text-indigo-900">Restaurar</button>
                            </form>
                            <form action="{{ route('todos.force-delete', $todo->id) }}" method="POST" onsubmit="return confirm('Esta acao e irreversivel. Excluir permanentemente?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Excluir permanentemente</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-gray-500">
                        A lixeira esta vazia.
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $todos->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
