#!/bin/bash
# Script de deploy para Hostinger shared hosting
# Executar no SSH após: git pull
# Uso: bash ~/salao-beleza/deploy-hostinger.sh

set -e

LARAVEL="/home/u567708141/salao-beleza"
TARGETS=(
    "/home/u567708141/public_html"
    "/home/u567708141/domains/salao.higordev.com.br/public_html"
)

INDEX_PHP='<?php
use Illuminate\Http\Request;
define("LARAVEL_START", microtime(true));
if (file_exists($m = "/home/u567708141/salao-beleza/storage/framework/maintenance.php")) { require $m; }
require "/home/u567708141/salao-beleza/vendor/autoload.php";
(require_once "/home/u567708141/salao-beleza/bootstrap/app.php")->handleRequest(Request::capture());'

HTACCESS='<IfModule mod_rewrite.c>
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
</IfModule>'

for TARGET in "${TARGETS[@]}"; do
    echo "→ Atualizando $TARGET"
    mkdir -p "$TARGET"

    # Copia os assets (remove antes para evitar build/build aninhado)
    rm -rf "$TARGET/build"
    cp -r "$LARAVEL/public/build" "$TARGET/build"

    # Grava index.php com caminhos absolutos
    echo "$INDEX_PHP" > "$TARGET/index.php"

    # Grava .htaccess correto
    echo "$HTACCESS" > "$TARGET/.htaccess"

    echo "   ✓ build, index.php e .htaccess atualizados"
done

# Laravel
cd "$LARAVEL"
php artisan migrate --force
php artisan config:cache
php artisan route:cache

echo ""
echo "✓ Deploy concluído!"
