# Spec de Deploy - Todo App Laravel no Railway

## Objetivo

Criar os arquivos de configuração e modificar os arquivos existentes para permitir o deploy do Todo App Laravel na plataforma Railway.

**URL Final:** `https://joaonogueira-todoapp.up.railway.app`

---

## Escopo

### Arquivos a Criar (4)

| Arquivo | Descrição |
|---------|-----------|
| `Procfile` | Comandos de inicialização e release |
| `nixpacks.toml` | Configuração de build com PHP 8.2 e Node.js 20 |
| `railway.json` | Configuração específica do Railway |
| `.env.production.example` | Template de variáveis de ambiente para produção |

### Arquivos a Modificar (2)

| Arquivo | Modificação |
|---------|-------------|
| `app/Providers/AppServiceProvider.php` | Adicionar forçar HTTPS em produção |
| `config/database.php` | Alterar `sslmode` de `prefer` para `require` |

---

## Implementação Detalhada

### 1. Criar `Procfile`

**Caminho:** `Procfile` (raiz do projeto)

```
web: php artisan serve --host=0.0.0.0 --port=$PORT
release: php artisan migrate --force && php artisan optimize
```

**Propósito:**
- `web`: Comando para iniciar o servidor PHP
- `release`: Executa migrations e otimizações antes de cada deploy

---

### 2. Criar `nixpacks.toml`

**Caminho:** `nixpacks.toml` (raiz do projeto)

```toml
[phases.setup]
nixPkgs = ["php82", "php82Extensions.pdo_pgsql", "php82Extensions.pgsql", "nodejs_20", "npm"]

[phases.install]
cmds = [
    "composer install --no-dev --optimize-autoloader",
    "npm ci",
    "npm run build"
]

[phases.build]
cmds = [
    "php artisan config:cache",
    "php artisan route:cache",
    "php artisan view:cache"
]

[start]
cmd = "php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"
```

**Propósito:**
- `setup`: Instala PHP 8.2 com extensões PostgreSQL e Node.js 20
- `install`: Instala dependências do Composer e NPM, compila assets
- `build`: Cacheia configurações do Laravel para performance
- `start`: Comando de inicialização do servidor

---

### 3. Criar `railway.json`

**Caminho:** `railway.json` (raiz do projeto)

```json
{
  "$schema": "https://railway.app/railway.schema.json",
  "build": {
    "builder": "NIXPACKS"
  },
  "deploy": {
    "startCommand": "php artisan serve --host=0.0.0.0 --port=$PORT",
    "healthcheckPath": "/up",
    "healthcheckTimeout": 300,
    "restartPolicyType": "ON_FAILURE",
    "restartPolicyMaxRetries": 10
  }
}
```

**Propósito:**
- Define Nixpacks como builder
- Configura health check no endpoint `/up` (padrão Laravel)
- Define política de restart em caso de falha

---

### 4. Criar `.env.production.example`

**Caminho:** `.env.production.example` (raiz do projeto)

```env
APP_NAME="Todo App"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://joaonogueira-todoapp.up.railway.app

APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stderr
LOG_LEVEL=error

DB_CONNECTION=pgsql
DATABASE_URL=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true

QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=log
```

**Propósito:**
- Template de referência para configuração de produção
- Usa `stderr` para logs (melhor para containers)
- Configura cookies seguros (HTTPS)

---

### 5. Modificar `AppServiceProvider.php`

**Caminho:** `app/Providers/AppServiceProvider.php`

**Código atual:**
```php
public function boot(): void
{
}
```

**Código novo:**
```php
public function boot(): void
{
    if (config('app.env') === 'production') {
        \Illuminate\Support\Facades\URL::forceScheme('https');
    }
}
```

**Propósito:**
- Railway usa proxy reverso, então URLs geradas pelo Laravel precisam ser forçadas para HTTPS
- Evita erros de mixed content (HTTP/HTTPS)

---

### 6. Modificar `config/database.php`

**Caminho:** `config/database.php` (linha 97)

**Código atual:**
```php
'sslmode' => env('DB_SSLMODE', 'prefer'),
```

**Código novo:**
```php
'sslmode' => env('DB_SSLMODE', 'require'),
```

**Propósito:**
- PostgreSQL do Railway requer conexão SSL
- `require` garante que a conexão sempre use SSL

---

## Variáveis de Ambiente no Railway

| Variável | Valor |
|----------|-------|
| `APP_NAME` | `Todo App` |
| `APP_ENV` | `production` |
| `APP_KEY` | `base64:...` (gerar localmente) |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://joaonogueira-todoapp.up.railway.app` |
| `DB_CONNECTION` | `pgsql` |
| `DATABASE_URL` | `${{Postgres.DATABASE_URL}}` |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `LOG_CHANNEL` | `stderr` |
| `SESSION_SECURE_COOKIE` | `true` |

---

## Verificação

### Checklist de Arquivos

- [ ] `Procfile` criado na raiz do projeto
- [ ] `nixpacks.toml` criado na raiz do projeto
- [ ] `railway.json` criado na raiz do projeto
- [ ] `.env.production.example` criado na raiz do projeto
- [ ] `AppServiceProvider.php` modificado com HTTPS forcing
- [ ] `config/database.php` modificado com sslmode require

### Testes Locais

```bash
# Verificar sintaxe do PHP
php -l app/Providers/AppServiceProvider.php
php -l config/database.php

# Verificar se os arquivos de configuração são válidos
php artisan config:cache
php artisan route:cache

# Limpar caches após teste
php artisan optimize:clear
```

### Testes Pós-Deploy

```bash
# Health check
curl https://joaonogueira-todoapp.up.railway.app/up
```

- [ ] Acessar URL e verificar HTTPS
- [ ] Registrar novo usuário
- [ ] Fazer login
- [ ] Criar categoria
- [ ] Criar tarefa
- [ ] Marcar tarefa como concluída
- [ ] Fazer logout

---

## Ordem de Execução

1. Criar `Procfile`
2. Criar `nixpacks.toml`
3. Criar `railway.json`
4. Criar `.env.production.example`
5. Modificar `AppServiceProvider.php`
6. Modificar `config/database.php`
7. Verificar sintaxe dos arquivos modificados
8. Commit e push para GitHub
9. Configurar projeto no Railway
