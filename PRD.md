# Plano de Implementação: Todo App Laravel

## Resumo

Implementação de um aplicativo de tarefas (Todo App) em Laravel 12.x seguindo as melhores práticas de 2025-2026, com autenticação de usuários, CRUD completo, categorias, API REST e interface em português.

## Decisões de Arquitetura

| Decisão | Escolha | Justificativa |
|---------|---------|---------------|
| Frontend | **Blade + Tailwind CSS** | Simples para CRUD, sem complexidade de SPA |
| Autenticação | **Laravel Breeze** | Moderno, leve, mantido oficialmente |
| Database | **MySQL** | Robusto, escolha do usuário |
| Soft Deletes | **Sim** | Permite recuperação de tarefas |
| Idioma | **Português BR** | Preferência do usuário |
| API | **Sim** | Endpoints REST para integração |

## Funcionalidades

### Core
- Autenticação completa (registro, login, logout, recuperação de senha)
- CRUD de tarefas (criar, listar, editar, deletar)
- Marcar tarefa como completa/pendente
- Níveis de prioridade (baixa, média, alta)
- Data de vencimento
- **Categorias/Tags** para organização
- Filtros (status, prioridade, categoria)
- Lixeira (soft delete com restauração)
- **API REST** com autenticação via Sanctum

## Estrutura de Arquivos

```
todoapp-laravel/
├── app/
│   ├── Enums/
│   │   └── Priority.php                 # Enum: low, medium, high
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── TodoController.php       # Controller web
│   │   │   ├── CategoryController.php   # Controller categorias
│   │   │   └── Api/
│   │   │       ├── TodoController.php   # API todos
│   │   │       └── CategoryController.php # API categorias
│   │   ├── Requests/
│   │   │   ├── StoreTodoRequest.php
│   │   │   ├── UpdateTodoRequest.php
│   │   │   ├── StoreCategoryRequest.php
│   │   │   └── UpdateCategoryRequest.php
│   │   └── Resources/
│   │       ├── TodoResource.php         # API Resource
│   │       └── CategoryResource.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Todo.php
│   │   └── Category.php                 # Nova model
│   └── Policies/
│       ├── TodoPolicy.php
│       └── CategoryPolicy.php
├── database/
│   ├── migrations/
│   │   ├── create_categories_table.php
│   │   └── create_todos_table.php
│   └── factories/
│       ├── TodoFactory.php
│       └── CategoryFactory.php
├── resources/
│   ├── lang/pt_BR/                      # Traduções
│   └── views/
│       ├── todos/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   └── trashed.blade.php
│       └── categories/
│           ├── index.blade.php
│           └── form.blade.php
├── routes/
│   ├── web.php                          # Rotas web
│   └── api.php                          # Rotas API
└── tests/
    └── Feature/
        ├── TodoTest.php
        ├── CategoryTest.php
        └── Api/
            └── TodoApiTest.php
```

## Passos de Implementação

### Fase 1: Setup do Projeto (5 passos)
1. Criar projeto: `composer create-project laravel/laravel todoapp-laravel`
2. Configurar `.env` com MySQL:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=todoapp
   DB_USERNAME=root
   DB_PASSWORD=
   ```
3. Instalar Breeze: `composer require laravel/breeze --dev`
4. Scaffold com Blade: `php artisan breeze:install blade`
5. Instalar Sanctum para API: `composer require laravel/sanctum`
6. Build assets: `npm install && npm run build`

### Fase 2: Configurar Português (3 passos)
1. Definir locale em `config/app.php`:
   ```php
   'locale' => 'pt_BR',
   'fallback_locale' => 'en',
   ```
2. Criar `lang/pt_BR/` com traduções de validação e mensagens
3. Traduzir views do Breeze (login, registro, etc.)

### Fase 3: Database - Migrations (3 passos)

**Migration: categories**
```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('color')->default('#6366f1'); // cor para badge
    $table->timestamps();

    $table->unique(['user_id', 'name']);
});
```

**Migration: todos**
```php
Schema::create('todos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
    $table->string('title');
    $table->text('description')->nullable();
    $table->boolean('completed')->default(false);
    $table->string('priority')->default('medium');
    $table->date('due_date')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->softDeletes();
    $table->timestamps();

    $table->index(['user_id', 'completed']);
    $table->index(['user_id', 'category_id']);
});
```

### Fase 4: Models (3 arquivos)

**Category Model:**
- `fillable`: name, color
- Relationships: `belongsTo(User)`, `hasMany(Todo)`
- Scope: `forUser($userId)`

**Todo Model:**
- `fillable`: title, description, completed, priority, due_date, category_id
- Casts: priority (enum), due_date (date), completed (bool)
- Relationships: `belongsTo(User)`, `belongsTo(Category)`
- Scopes: `completed()`, `pending()`, `overdue()`, `byCategory($id)`
- Methods: `toggleComplete()`, `isOverdue()`

**User Model (adicionar):**
- `hasMany(Todo)`, `hasMany(Category)`

### Fase 5: Controllers Web (2 controllers)

**CategoryController:**
- `index()` - listar categorias
- `store()` - criar categoria
- `update()` - editar categoria
- `destroy()` - deletar categoria

**TodoController:**
- `index()` - listar com filtros e estatísticas
- `create()`, `store()` - criar tarefa
- `edit()`, `update()` - editar tarefa
- `toggle()` - alternar status completo
- `destroy()` - soft delete
- `trashed()`, `restore()`, `forceDelete()` - lixeira

### Fase 6: API REST (2 controllers + resources)

**Rotas API** (`routes/api.php`):
```php
Route::middleware('auth:sanctum')->group(function () {
    // Categorias
    Route::apiResource('categories', Api\CategoryController::class);

    // Todos
    Route::apiResource('todos', Api\TodoController::class);
    Route::patch('todos/{todo}/toggle', [Api\TodoController::class, 'toggle']);
});
```

**API Resources:**
- `TodoResource` - formata JSON da tarefa
- `CategoryResource` - formata JSON da categoria
- `TodoCollection` - paginação

### Fase 7: Views em Português (6 views)

**Estrutura das views:**
1. `todos/index.blade.php` - "Minhas Tarefas", cards de estatísticas, filtros, lista
2. `todos/create.blade.php` - "Nova Tarefa", formulário completo
3. `todos/edit.blade.php` - "Editar Tarefa"
4. `todos/trashed.blade.php` - "Lixeira"
5. `categories/index.blade.php` - "Categorias", lista com cores
6. `categories/form.blade.php` - Modal/form para criar/editar

**Textos em português:**
- Botões: "Salvar", "Cancelar", "Excluir", "Restaurar"
- Labels: "Título", "Descrição", "Prioridade", "Data de Vencimento", "Categoria"
- Status: "Pendente", "Concluída", "Atrasada"
- Prioridades: "Baixa", "Média", "Alta"
- Mensagens: "Tarefa criada com sucesso!", "Tarefa atualizada!", etc.

### Fase 8: Rotas Web

```php
Route::middleware('auth')->group(function () {
    // Categorias
    Route::resource('categorias', CategoryController::class)->except(['show']);

    // Tarefas
    Route::prefix('tarefas')->name('todos.')->group(function () {
        Route::get('/', [TodoController::class, 'index'])->name('index');
        Route::get('/criar', [TodoController::class, 'create'])->name('create');
        Route::post('/', [TodoController::class, 'store'])->name('store');
        Route::get('/{todo}/editar', [TodoController::class, 'edit'])->name('edit');
        Route::put('/{todo}', [TodoController::class, 'update'])->name('update');
        Route::patch('/{todo}/alternar', [TodoController::class, 'toggle'])->name('toggle');
        Route::delete('/{todo}', [TodoController::class, 'destroy'])->name('destroy');
        Route::get('/lixeira', [TodoController::class, 'trashed'])->name('trashed');
        Route::patch('/lixeira/{id}/restaurar', [TodoController::class, 'restore'])->name('restore');
        Route::delete('/lixeira/{id}', [TodoController::class, 'forceDelete'])->name('force-delete');
    });
});
```

### Fase 9: Testes (2 arquivos)

**tests/Feature/TodoTest.php:**
- Autenticação requerida
- CRUD funcionando
- Isolamento por usuário
- Validações
- Soft delete e restore

**tests/Feature/Api/TodoApiTest.php:**
- Autenticação via Sanctum
- Endpoints retornando JSON correto
- Paginação funcionando

### Fase 10: Finalização

1. Criar factories e seeders
2. Rodar migrations: `php artisan migrate`
3. Seed dados de exemplo: `php artisan db:seed`
4. Rodar testes: `php artisan test`
5. Build final: `npm run build`

## Verificação

```bash
# 1. Criar banco MySQL
mysql -u root -p -e "CREATE DATABASE todoapp"

# 2. Rodar migrations
php artisan migrate

# 3. Seed dados de teste
php artisan db:seed

# 4. Iniciar servidor
php artisan serve

# 5. Testar no navegador
# - Acessar http://localhost:8000
# - Registrar usuário
# - Criar categoria "Trabalho"
# - Criar tarefa com categoria e data
# - Marcar como concluída
# - Testar filtros
# - Deletar e restaurar

# 6. Testar API (com token)
curl -X GET http://localhost:8000/api/todos \
  -H "Authorization: Bearer {token}"

# 7. Rodar testes
php artisan test
```

## Fontes de Referência

- [Laravel Best Practices 2025-2026](https://smartlogiceg.com/en/post/laravel-best-practices-for-2026)
- [Laravel 12 Todo Tutorial](https://kritimyantra.com/blogs/laravel-12-todo-app-tutorial-with-auth-system-beginner-friendly-guide)
- [Laravel Eloquent Documentation](https://laravel.com/docs/11.x/eloquent)
- [Laravel Sanctum API Auth](https://laravel.com/docs/11.x/sanctum)
- [GitHub: milon/laravel-todo](https://github.com/milon/laravel-todo)
