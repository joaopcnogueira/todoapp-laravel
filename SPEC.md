# Especificação Técnica: Todo App Laravel

## 1. Visão Geral

### 1.1 Objetivo
Sistema de gerenciamento de tarefas (Todo App) com autenticação de usuários, categorização, priorização e API REST completa.

### 1.2 Stack Tecnológica
| Componente | Tecnologia | Versão |
|------------|------------|--------|
| Framework Backend | Laravel | 12.x |
| Frontend | Blade + Tailwind CSS | 3.x |
| Banco de Dados | MySQL | 8.x |
| Autenticação Web | Laravel Breeze | - |
| Autenticação API | Laravel Sanctum | - |
| Runtime | PHP | 8.2+ |

---

## 2. Modelo de Dados

### 2.1 Diagrama ER

```
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│   users     │       │ categories  │       │    todos    │
├─────────────┤       ├─────────────┤       ├─────────────┤
│ id (PK)     │───┐   │ id (PK)     │───┐   │ id (PK)     │
│ name        │   │   │ user_id(FK) │←──┼───│ user_id(FK) │
│ email       │   └──→│ name        │   │   │ category_id │
│ password    │       │ color       │   └──→│ title       │
│ timestamps  │       │ timestamps  │       │ description │
└─────────────┘       └─────────────┘       │ completed   │
                                            │ priority    │
                                            │ due_date    │
                                            │ completed_at│
                                            │ deleted_at  │
                                            │ timestamps  │
                                            └─────────────┘
```

### 2.2 Tabela: `users` (Laravel Breeze)
| Coluna | Tipo | Constraints | Descrição |
|--------|------|-------------|-----------|
| id | BIGINT | PK, AUTO_INCREMENT | Identificador único |
| name | VARCHAR(255) | NOT NULL | Nome do usuário |
| email | VARCHAR(255) | NOT NULL, UNIQUE | Email (login) |
| email_verified_at | TIMESTAMP | NULLABLE | Data verificação |
| password | VARCHAR(255) | NOT NULL | Senha hash bcrypt |
| remember_token | VARCHAR(100) | NULLABLE | Token sessão |
| created_at | TIMESTAMP | | Data criação |
| updated_at | TIMESTAMP | | Data atualização |

### 2.3 Tabela: `categories`
| Coluna | Tipo | Constraints | Descrição |
|--------|------|-------------|-----------|
| id | BIGINT | PK, AUTO_INCREMENT | Identificador único |
| user_id | BIGINT | FK → users.id, CASCADE DELETE | Proprietário |
| name | VARCHAR(255) | NOT NULL | Nome da categoria |
| color | VARCHAR(7) | DEFAULT '#6366f1' | Cor hexadecimal |
| created_at | TIMESTAMP | | Data criação |
| updated_at | TIMESTAMP | | Data atualização |

**Índices:**
- UNIQUE(`user_id`, `name`) - Nomes únicos por usuário

### 2.4 Tabela: `todos`
| Coluna | Tipo | Constraints | Descrição |
|--------|------|-------------|-----------|
| id | BIGINT | PK, AUTO_INCREMENT | Identificador único |
| user_id | BIGINT | FK → users.id, CASCADE DELETE | Proprietário |
| category_id | BIGINT | FK → categories.id, NULL ON DELETE, NULLABLE | Categoria |
| title | VARCHAR(255) | NOT NULL | Título da tarefa |
| description | TEXT | NULLABLE | Descrição detalhada |
| completed | BOOLEAN | DEFAULT false | Status conclusão |
| priority | VARCHAR(20) | DEFAULT 'medium' | Prioridade (enum) |
| due_date | DATE | NULLABLE | Data vencimento |
| completed_at | TIMESTAMP | NULLABLE | Data conclusão |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete |
| created_at | TIMESTAMP | | Data criação |
| updated_at | TIMESTAMP | | Data atualização |

**Índices:**
- INDEX(`user_id`, `completed`) - Filtro por status
- INDEX(`user_id`, `category_id`) - Filtro por categoria

### 2.5 Enum: Priority
```php
enum Priority: string
{
    case LOW = 'low';       // Baixa
    case MEDIUM = 'medium'; // Média
    case HIGH = 'high';     // Alta
}
```

---

## 3. Regras de Negócio

### 3.1 Usuários
- RN01: Email deve ser único no sistema
- RN02: Senha mínima de 8 caracteres
- RN03: Usuário só acessa suas próprias tarefas e categorias

### 3.2 Categorias
- RN04: Nome da categoria único por usuário
- RN05: Cor padrão: `#6366f1` (indigo)
- RN06: Deletar categoria não deleta tarefas (seta `category_id` para NULL)

### 3.3 Tarefas
- RN07: Título obrigatório, máximo 255 caracteres
- RN08: Prioridade padrão: `medium`
- RN09: Ao marcar como concluída, `completed_at` é preenchido automaticamente
- RN10: Ao desmarcar concluída, `completed_at` é limpo
- RN11: Tarefa está "atrasada" quando: `due_date < hoje` AND `completed = false`
- RN12: Soft delete permite recuperação por 30 dias
- RN13: Tarefas ordenadas por: prioridade (alta → baixa), depois data vencimento

---

## 4. API REST

### 4.1 Autenticação
Todas as rotas API requerem header:
```
Authorization: Bearer {sanctum_token}
```

### 4.2 Endpoints de Categorias

#### GET /api/categories
Lista categorias do usuário autenticado.

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Trabalho",
      "color": "#6366f1",
      "todos_count": 5,
      "created_at": "2025-01-15T10:00:00Z"
    }
  ]
}
```

#### POST /api/categories
Cria nova categoria.

**Request:**
```json
{
  "name": "Pessoal",
  "color": "#22c55e"
}
```

**Validação:**
| Campo | Regras |
|-------|--------|
| name | required, string, max:255, unique por usuário |
| color | nullable, regex:/^#[0-9A-Fa-f]{6}$/ |

**Response 201:**
```json
{
  "data": {
    "id": 2,
    "name": "Pessoal",
    "color": "#22c55e"
  },
  "message": "Categoria criada com sucesso!"
}
```

#### PUT /api/categories/{id}
Atualiza categoria existente.

#### DELETE /api/categories/{id}
Remove categoria.

---

### 4.3 Endpoints de Tarefas

#### GET /api/todos
Lista tarefas com filtros e paginação.

**Query Parameters:**
| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| status | string | `all`, `pending`, `completed` |
| priority | string | `low`, `medium`, `high` |
| category_id | integer | Filtrar por categoria |
| search | string | Busca no título/descrição |
| page | integer | Página (default: 1) |
| per_page | integer | Itens por página (default: 15, max: 50) |

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Estudar Laravel",
      "description": "Completar tutorial de API",
      "completed": false,
      "priority": "high",
      "priority_label": "Alta",
      "due_date": "2025-01-20",
      "is_overdue": false,
      "category": {
        "id": 1,
        "name": "Trabalho",
        "color": "#6366f1"
      },
      "created_at": "2025-01-15T10:00:00Z",
      "updated_at": "2025-01-15T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42
  },
  "stats": {
    "total": 42,
    "completed": 20,
    "pending": 22,
    "overdue": 3
  }
}
```

#### POST /api/todos
Cria nova tarefa.

**Request:**
```json
{
  "title": "Implementar API",
  "description": "Criar endpoints REST",
  "priority": "high",
  "due_date": "2025-01-25",
  "category_id": 1
}
```

**Validação:**
| Campo | Regras |
|-------|--------|
| title | required, string, max:255 |
| description | nullable, string, max:5000 |
| priority | nullable, in:low,medium,high |
| due_date | nullable, date, after_or_equal:today |
| category_id | nullable, exists:categories,id (do usuário) |

**Response 201:**
```json
{
  "data": { ... },
  "message": "Tarefa criada com sucesso!"
}
```

#### GET /api/todos/{id}
Retorna tarefa específica.

#### PUT /api/todos/{id}
Atualiza tarefa existente.

#### PATCH /api/todos/{id}/toggle
Alterna status completo/pendente.

**Response 200:**
```json
{
  "data": { ... },
  "message": "Tarefa marcada como concluída!"
}
```

#### DELETE /api/todos/{id}
Move tarefa para lixeira (soft delete).

**Response 200:**
```json
{
  "message": "Tarefa movida para lixeira!"
}
```

#### GET /api/todos/trashed
Lista tarefas na lixeira.

#### PATCH /api/todos/{id}/restore
Restaura tarefa da lixeira.

#### DELETE /api/todos/{id}/force
Remove permanentemente.

---

## 5. Interface Web

### 5.1 Rotas Web
| Método | URI | Nome | Descrição |
|--------|-----|------|-----------|
| GET | /tarefas | todos.index | Lista tarefas |
| GET | /tarefas/criar | todos.create | Formulário criação |
| POST | /tarefas | todos.store | Salvar tarefa |
| GET | /tarefas/{id}/editar | todos.edit | Formulário edição |
| PUT | /tarefas/{id} | todos.update | Atualizar tarefa |
| PATCH | /tarefas/{id}/alternar | todos.toggle | Alternar status |
| DELETE | /tarefas/{id} | todos.destroy | Soft delete |
| GET | /tarefas/lixeira | todos.trashed | Lista lixeira |
| PATCH | /tarefas/lixeira/{id}/restaurar | todos.restore | Restaurar |
| DELETE | /tarefas/lixeira/{id} | todos.force-delete | Deletar permanente |
| GET | /categorias | categorias.index | Lista categorias |
| POST | /categorias | categorias.store | Criar categoria |
| PUT | /categorias/{id} | categorias.update | Atualizar categoria |
| DELETE | /categorias/{id} | categorias.destroy | Deletar categoria |

### 5.2 Layouts e Componentes

#### Dashboard (todos.index)
```
┌────────────────────────────────────────────────────────────┐
│ [Logo] Minhas Tarefas                    [User ▼] [Logout] │
├────────────────────────────────────────────────────────────┤
│ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐       │
│ │  Total   │ │Pendentes │ │Concluídas│ │ Atrasadas│       │
│ │    42    │ │    22    │ │    20    │ │    3     │       │
│ └──────────┘ └──────────┘ └──────────┘ └──────────┘       │
├────────────────────────────────────────────────────────────┤
│ [+ Nova Tarefa]  [Filtros ▼]  [🔍 Buscar...]              │
├────────────────────────────────────────────────────────────┤
│ ☐ Implementar API          [Alta]   Trabalho   20/01/2025 │
│ ☑ Estudar Laravel          [Média]  Trabalho   ✓Concluída │
│ ☐ Comprar materiais        [Baixa]  Pessoal    ⚠️Atrasada  │
└────────────────────────────────────────────────────────────┘
```

### 5.3 Textos e Mensagens (pt_BR)

**Labels:**
| Chave | Texto |
|-------|-------|
| title | Título |
| description | Descrição |
| priority | Prioridade |
| due_date | Data de Vencimento |
| category | Categoria |
| status | Status |

**Prioridades:**
| Valor | Label | Cor Badge |
|-------|-------|-----------|
| low | Baixa | green |
| medium | Média | yellow |
| high | Alta | red |

**Status:**
| Estado | Label | Cor |
|--------|-------|-----|
| pending | Pendente | gray |
| completed | Concluída | green |
| overdue | Atrasada | red |

**Mensagens Flash:**
| Ação | Mensagem |
|------|----------|
| todo.created | Tarefa criada com sucesso! |
| todo.updated | Tarefa atualizada com sucesso! |
| todo.completed | Tarefa marcada como concluída! |
| todo.pending | Tarefa marcada como pendente! |
| todo.trashed | Tarefa movida para lixeira! |
| todo.restored | Tarefa restaurada com sucesso! |
| todo.deleted | Tarefa excluída permanentemente! |
| category.created | Categoria criada com sucesso! |
| category.updated | Categoria atualizada com sucesso! |
| category.deleted | Categoria excluída com sucesso! |

---

## 6. Segurança

### 6.1 Autenticação
- Senhas hashadas com bcrypt (custo 12)
- Proteção CSRF em formulários web
- Rate limiting: 60 requests/minuto por IP
- Tokens Sanctum expiram em 7 dias

### 6.2 Autorização (Policies)
```php
// TodoPolicy
public function view(User $user, Todo $todo): bool
{
    return $user->id === $todo->user_id;
}

public function update(User $user, Todo $todo): bool
{
    return $user->id === $todo->user_id;
}

public function delete(User $user, Todo $todo): bool
{
    return $user->id === $todo->user_id;
}
```

### 6.3 Validação de Input
- Sanitização automática pelo Laravel
- Validação em Form Requests
- Escape de output no Blade (`{{ }}`)

---

## 7. Testes

### 7.1 Testes de Feature (PHPUnit)

**TodoTest.php:**
```php
// Casos de teste
- test_user_can_view_own_todos()
- test_user_cannot_view_others_todos()
- test_user_can_create_todo()
- test_todo_requires_title()
- test_user_can_update_own_todo()
- test_user_can_toggle_todo_status()
- test_user_can_soft_delete_todo()
- test_user_can_restore_todo()
- test_user_can_force_delete_todo()
- test_todo_filters_work_correctly()
```

**CategoryTest.php:**
```php
- test_user_can_create_category()
- test_category_name_unique_per_user()
- test_deleting_category_nullifies_todos()
```

**Api/TodoApiTest.php:**
```php
- test_api_requires_authentication()
- test_api_lists_todos_with_pagination()
- test_api_creates_todo()
- test_api_updates_todo()
- test_api_toggles_todo()
- test_api_soft_deletes_todo()
```

### 7.2 Cobertura Mínima
- Controllers: 90%
- Models: 95%
- Policies: 100%

---

## 8. Performance

### 8.1 Queries Otimizadas
- Eager loading de relacionamentos
- Índices nas colunas de busca
- Paginação em listagens

### 8.2 Cache
- Cache de contadores (opcional)
- Cache de categorias por usuário (opcional)

---

## 9. Deploy Checklist

- [ ] Configurar variáveis de ambiente produção
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] `npm run build`
- [ ] Configurar SSL/HTTPS
- [ ] Configurar backup de banco de dados
- [ ] Configurar logs (Laravel Telescope em dev)

---

## 10. Changelog

| Versão | Data | Descrição |
|--------|------|-----------|
| 1.0.0 | 2025-01-21 | Especificação inicial |
