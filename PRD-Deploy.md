# PRD de Deploy: Todo App Laravel

## Resumo

Deploy gratuito do Todo App Laravel no **Railway** com URL `https://joaonogueira-todoapp.up.railway.app`.

---

## Plataforma Escolhida: Railway

| Aspecto | Detalhes |
|---------|----------|
| **URL Final** | `https://joaonogueira-todoapp.up.railway.app` |
| **Free Tier** | $5/mês em créditos (suficiente para apps pequenos) |
| **Database** | PostgreSQL incluído gratuitamente |
| **SSL** | Certificado Let's Encrypt automático |
| **Build** | Detecção automática de Laravel via Nixpacks |

---

## Requisitos do Servidor

### PHP 8.2+ com Extensões
- Ctype, cURL, DOM, Fileinfo, Filter, Hash
- Mbstring, OpenSSL, PCRE, PDO, PDO_PGSQL
- Session, Tokenizer, XML

### Diretórios com Permissão de Escrita
- `storage/`
- `bootstrap/cache/`

---

## Arquivos a Criar

### 1. `Procfile`

```
web: php artisan serve --host=0.0.0.0 --port=$PORT
release: php artisan migrate --force && php artisan optimize
```

### 2. `nixpacks.toml`

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

### 3. `railway.json`

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

### 4. `.env.production.example`

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

---

## Arquivos a Modificar

### 1. `app/Providers/AppServiceProvider.php`

Adicionar no método `boot()`:

```php
public function boot(): void
{
    // Forçar HTTPS em produção (Railway usa proxy reverso)
    if (config('app.env') === 'production') {
        \Illuminate\Support\Facades\URL::forceScheme('https');
    }
}
```

### 2. `config/database.php`

Alterar `sslmode` na conexão `pgsql` de `'prefer'` para `'require'`:

```php
'pgsql' => [
    // ... outras configurações
    'sslmode' => env('DB_SSLMODE', 'require'),
],
```

---

## Variáveis de Ambiente no Railway

| Variável | Valor |
|----------|-------|
| `APP_NAME` | `Todo App` |
| `APP_ENV` | `production` |
| `APP_KEY` | `base64:...` (gerar com `php artisan key:generate --show`) |
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

## Passo a Passo de Deploy

### Pré-requisitos
1. Conta no GitHub com repositório do projeto
2. Conta no Railway (criar em https://railway.app com GitHub)

### Etapa 1: Preparar Repositório

```bash
# Na pasta do projeto
cd /Users/joaonogueira/Github/todoapp-laravel

# Inicializar git (se necessário)
git init

# Criar arquivos de deploy (Procfile, nixpacks.toml, railway.json)
# Modificar AppServiceProvider.php

# Commit das alterações
git add .
git commit -m "Adicionar configuração de deploy Railway"

# Push para GitHub
git remote add origin https://github.com/joaonogueira/todoapp-laravel.git
git push -u origin main
```

### Etapa 2: Criar Projeto no Railway

1. Acessar https://railway.app
2. Clicar em **"New Project"**
3. Selecionar **"Deploy from GitHub repo"**
4. Autorizar Railway a acessar seu GitHub
5. Selecionar o repositório `todoapp-laravel`

### Etapa 3: Adicionar Banco PostgreSQL

1. No dashboard Railway, clicar em **"+ New"**
2. Selecionar **"Database"** > **"PostgreSQL"**
3. Railway vincula automaticamente ao projeto

### Etapa 4: Configurar Variáveis de Ambiente

1. No dashboard, ir ao serviço web > **"Variables"**
2. Adicionar cada variável da tabela acima
3. Para `DATABASE_URL`, usar: `${{Postgres.DATABASE_URL}}`

**Gerar APP_KEY localmente:**
```bash
php artisan key:generate --show
```

### Etapa 5: Configurar Domínio Personalizado

1. Ir em **Settings** > **Networking**
2. Clicar em **"Generate Domain"**
3. Editar para: `joaonogueira-todoapp`
4. URL final: `https://joaonogueira-todoapp.up.railway.app`

### Etapa 6: Deploy

- Push para branch `main` dispara deploy automático
- Ou clicar em **"Deploy"** no dashboard Railway

### Etapa 7: Executar Migrations (se necessário)

```bash
# Instalar CLI Railway
npm install -g @railway/cli

# Login
railway login

# Linkar projeto
railway link

# Executar migrations
railway run php artisan migrate --force
```

---

## Verificação Pós-Deploy

### 1. Health Check
```bash
curl https://joaonogueira-todoapp.up.railway.app/up
```

### 2. Testes Funcionais
- [ ] Acessar URL e verificar HTTPS
- [ ] Registrar novo usuário
- [ ] Fazer login
- [ ] Criar categoria
- [ ] Criar tarefa com categoria e data
- [ ] Marcar tarefa como concluída
- [ ] Testar filtros
- [ ] Deletar e restaurar tarefa
- [ ] Fazer logout

### 3. Monitoramento
- Verificar logs no dashboard Railway
- Monitorar uso de recursos (memória, CPU)

---

## Troubleshooting

### Erro: Mixed Content (HTTP/HTTPS)
**Causa:** Assets carregando via HTTP
**Solução:** Verificar se `URL::forceScheme('https')` está no AppServiceProvider

### Erro: Session Not Persisting
**Causa:** Cookies não persistindo
**Solução:**
- Verificar `SESSION_DRIVER=database`
- Verificar `SESSION_SECURE_COOKIE=true`
- Confirmar que migrations rodaram

### Erro: Database Connection Refused
**Causa:** Conexão PostgreSQL falhando
**Solução:**
- Verificar `DATABASE_URL` está configurada
- Verificar `DB_CONNECTION=pgsql`
- Verificar `sslmode=require`

### Erro: Build Failures
**Causa:** Dependências não instalando
**Solução:**
- Verificar `composer.lock` está commitado
- Verificar `package-lock.json` está commitado
- Verificar versões PHP/Node no nixpacks.toml

### Erro: 502 Bad Gateway
**Causa:** App não iniciando
**Solução:**
- Verificar logs no Railway
- Verificar Procfile está correto
- Verificar APP_KEY está definida

---

## Comandos Úteis

```bash
# Gerar APP_KEY
php artisan key:generate --show

# Otimizar para produção
php artisan optimize

# Limpar caches
php artisan optimize:clear

# Executar migrations
php artisan migrate --force

# Ver status do banco
php artisan migrate:status

# Railway CLI
railway login
railway link
railway run <comando>
railway logs
```

---

## Custos

| Serviço | Custo |
|---------|-------|
| Railway (web + db) | $0 - $5/mês (free tier) |
| Domínio personalizado | Opcional (~$10-15/ano) |
| **Total** | **$0/mês** (dentro do free tier) |

---

## Fontes de Referência

- [Laravel 12 Deployment Documentation](https://laravel.com/docs/12.x/deployment)
- [Railway Laravel Guide](https://docs.railway.com/guides/laravel)
- [Railway Public Domains](https://docs.railway.com/reference/public-domains)
- [Railway Environment Variables](https://docs.railway.com/guides/variables)
- [Nixpacks PHP Configuration](https://nixpacks.com/docs/providers/php)
