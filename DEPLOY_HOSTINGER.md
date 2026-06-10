# Deploy — Hostinger Shared Hosting

Guia completo para subir o projeto Laravel + Vue 3 + Inertia em hospedagem compartilhada Hostinger.

---

## Pré-requisitos

- Conta Hostinger com plano que inclua SSH
- Repositório no GitHub (privado ou público)
- Personal Access Token do GitHub com permissão `repo`
- Banco de dados MySQL criado no painel Hostinger

---

## 1. Acesso SSH

No painel Hostinger → **Avançado → Acesso SSH**, copie os dados de conexão.

```bash
ssh -p PORTA USUARIO@IP_DO_SERVIDOR
```

> Substitua PORTA, USUARIO e IP pelos valores do painel.

---

## 2. Clonar o repositório

```bash
cd ~
git clone https://SEU_USUARIO_GITHUB:SEU_TOKEN@github.com/SEU_USUARIO_GITHUB/NOME_DO_REPO.git salao-beleza
cd salao-beleza
```

> **Nunca commite ou exponha o token.** Gere em: GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic) → permissão `repo`.

---

## 3. Instalar dependências PHP

```bash
composer install --no-dev --optimize-autoloader
```

---

## 4. Configurar o .env

```bash
cp .env.example .env
php artisan key:generate
nano .env
```

Preencha as variáveis obrigatórias:

```dotenv
APP_NAME="Salão de Beleza"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://SEU_DOMINIO.com.br

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=NOME_DO_BANCO
DB_USERNAME=USUARIO_DO_BANCO
DB_PASSWORD=SENHA_DO_BANCO

APP_TIMEZONE=America/Sao_Paulo
APP_LOCALE=pt_BR

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

> Crie o banco em: Hostinger → **Bancos de dados → MySQL → Criar novo banco**.

---

## 5. Criar pastas de storage obrigatórias

O Laravel precisa dessas pastas para funcionar. O Hostinger não as cria automaticamente:

```bash
mkdir -p storage/framework/views
mkdir -p storage/framework/sessions
mkdir -p storage/framework/cache/data
mkdir -p storage/app/public
mkdir -p storage/logs

chmod -R 777 storage/
chmod -R 777 bootstrap/cache/
```

---

## 6. Configurar o Document Root (passo crítico)

O Hostinger serve o domínio a partir de `~/domains/SEU_DOMINIO/public_html/`.  
O Laravel precisa que o webroot aponte para a pasta `public/` do projeto.  
**Não use symlinks** — o mod_rewrite não funciona em diretórios linkados no Hostinger.

```bash
# Remove o public_html padrão
rm -rf ~/domains/SEU_DOMINIO/public_html

# Cria pasta real
mkdir -p ~/domains/SEU_DOMINIO/public_html

# Copia o conteúdo da pasta public do Laravel
cp -r ~/salao-beleza/public/. ~/domains/SEU_DOMINIO/public_html/

# Cria o index.php com caminhos absolutos para o projeto
cat > ~/domains/SEU_DOMINIO/public_html/index.php << 'EOF'
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = '/home/SEU_USUARIO/salao-beleza/storage/framework/maintenance.php')) {
    require $maintenance;
}

require '/home/SEU_USUARIO/salao-beleza/vendor/autoload.php';

(require_once '/home/SEU_USUARIO/salao-beleza/bootstrap/app.php')
    ->handleRequest(Request::capture());
EOF
```

> Se o site for o **domínio principal** da conta, repita o processo para `~/public_html/` no lugar de `~/domains/SEU_DOMINIO/public_html/`.

---

## 7. Configurar o .htaccess

O `RewriteBase /` é obrigatório no Hostinger para o roteamento do Laravel funcionar:

```bash
cat > ~/domains/SEU_DOMINIO/public_html/.htaccess << 'HTEOF'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
HTEOF
```

---

## 8. Rodar migrations e seeders

```bash
cd ~/salao-beleza
php artisan migrate --force
php artisan db:seed --class=AdminUserSeeder --force
```

---

## 9. Otimizar para produção

```bash
php artisan config:cache
php artisan route:cache
```

> `view:cache` falha em shared hosting — pode ignorar.

---

## 10. Verificar funcionamento

```bash
# Cria arquivo de teste
echo "<?php echo 'PHP OK - ' . phpversion();" > ~/domains/SEU_DOMINIO/public_html/teste.php
```

Acesse `SEU_DOMINIO/teste.php` → deve aparecer `PHP OK - 8.x.x`.  
Depois delete: `rm ~/domains/SEU_DOMINIO/public_html/teste.php`

---

## Atualizações futuras (deploy contínuo)

Sempre que fizer push de novas funcionalidades:

```bash
# 1. Build local (Hostinger não tem Node.js)
docker compose run --rm node yarn build
git add public/build
git commit -m "chore: build assets"
git push

# 2. No SSH do Hostinger
cd ~/salao-beleza
git pull

# 3. Copiar assets atualizados para o public_html
cp -r ~/salao-beleza/public/build ~/domains/SEU_DOMINIO/public_html/build

# 4. Se tiver migrations novas
php artisan migrate --force

# 5. Limpar caches
php artisan config:cache
php artisan route:cache
```

---

## Problemas encontrados e soluções

| Problema | Causa | Solução |
|---|---|---|
| 403 Forbidden | Symlink no public_html | Substituir symlink por pasta real com arquivos copiados |
| 404 em todas as rotas exceto `/` | mod_rewrite sem `RewriteBase /` | Adicionar `RewriteBase /` no `.htaccess` |
| 500 Internal Server Error | Pastas de storage inexistentes | Criar `storage/framework/{views,sessions,cache/data}` manualmente |
| `view:cache` falha | Caminho de views inválido em shared hosting | Ignorar — não é necessário em produção |
| `cache:clear` falha | Permissões no storage | `chmod -R 777 storage/ bootstrap/cache/` |
| Clone do GitHub falha | Autenticação por senha desativada | Usar Personal Access Token no lugar da senha |

---

## Observações de segurança

- **Nunca commite** o arquivo `.env`
- **Nunca exponha** tokens do GitHub em commits ou logs
- **Delete** o `teste.php` após confirmar que o servidor está ok
- Mantenha `APP_DEBUG=false` em produção
