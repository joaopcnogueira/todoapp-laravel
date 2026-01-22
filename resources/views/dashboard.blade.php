<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Card -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 overflow-hidden shadow-lg rounded-xl mb-6">
                <div class="p-6 text-white">
                    <h3 class="text-2xl font-bold mb-2">Olá, {{ Auth::user()->name }}!</h3>
                    <p class="text-white/80">Bem-vindo ao seu gerenciador de tarefas. Organize seu dia de forma eficiente.</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Tarefas Card -->
                <a href="{{ route('todos.index') }}" class="bg-white overflow-hidden shadow-md rounded-xl hover:shadow-lg transition-shadow group">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                                <svg class="w-6 h-6 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-800 mb-1">Minhas Tarefas</h4>
                        <p class="text-gray-500 text-sm">Visualize e gerencie todas as suas tarefas</p>
                    </div>
                </a>

                <!-- Categorias Card -->
                <a href="{{ route('categories.index') }}" class="bg-white overflow-hidden shadow-md rounded-xl hover:shadow-lg transition-shadow group">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-200 transition-colors">
                                <svg class="w-6 h-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                                </svg>
                            </div>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-800 mb-1">Categorias</h4>
                        <p class="text-gray-500 text-sm">Organize suas tarefas por categorias</p>
                    </div>
                </a>

                <!-- Nova Tarefa Card -->
                <a href="{{ route('todos.create') }}" class="bg-white overflow-hidden shadow-md rounded-xl hover:shadow-lg transition-shadow group border-2 border-dashed border-gray-200 hover:border-purple-300">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center group-hover:bg-purple-100 transition-colors">
                                <svg class="w-6 h-6 text-gray-400 group-hover:text-purple-600 transition-colors" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </div>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-800 mb-1">Nova Tarefa</h4>
                        <p class="text-gray-500 text-sm">Adicione uma nova tarefa à sua lista</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
