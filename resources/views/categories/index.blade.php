<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Categorias
            </h2>
            <a href="{{ route('todos.index') }}" class="text-indigo-600 hover:text-indigo-900">Voltar para tarefas</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Create Category Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Nova Categoria</h3>
                <form method="POST" action="{{ route('categories.store') }}" class="flex gap-4 items-end">
                    @csrf
                    <div class="flex-1">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                        <input type="text" name="name" id="name" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="color" class="block text-sm font-medium text-gray-700 mb-1">Cor</label>
                        <input type="color" name="color" id="color" value="#6366f1" class="h-10 w-20 rounded-md border-gray-300">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Criar</button>
                </form>
            </div>

            <!-- Categories List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 p-6 border-b">Suas Categorias</h3>
                @forelse($categories as $category)
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between hover:bg-gray-50" x-data="{ editing: false }">
                        <div class="flex items-center gap-4" x-show="!editing">
                            <div class="w-6 h-6 rounded-full" style="background-color: {{ $category->color }}"></div>
                            <span class="font-medium text-gray-900">{{ $category->name }}</span>
                            <span class="text-sm text-gray-500">({{ $category->todos_count }} tarefas)</span>
                        </div>

                        <!-- Edit Form (hidden by default) -->
                        <form method="POST" action="{{ route('categories.update', $category) }}" class="flex-1 flex gap-4 items-center" x-show="editing" style="display: none;">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $category->name }}" required class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="color" name="color" value="{{ $category->color }}" class="h-10 w-20 rounded-md border-gray-300">
                            <button type="submit" class="text-indigo-600 hover:text-indigo-900">Salvar</button>
                            <button type="button" @click="editing = false" class="text-gray-500 hover:text-gray-700">Cancelar</button>
                        </form>

                        <div class="flex items-center gap-4" x-show="!editing">
                            <button @click="editing = true" class="text-indigo-600 hover:text-indigo-900">Editar</button>
                            <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Excluir categoria? As tarefas nao serao excluidas.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Excluir</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-gray-500">
                        Nenhuma categoria criada ainda.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="//unpkg.com/alpinejs" defer></script>
    @endpush
</x-app-layout>
