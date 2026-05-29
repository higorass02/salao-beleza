# Projeto Salão Agendamento

Este projeto é um scaffold inicial do sistema de agendamento para salão de beleza com Laravel + Vue 3 + Inertia.

## Estrutura criada

- `docker-compose.yml`
- `docker/php/Dockerfile`
- `docker/nginx/default.conf`
- `.env.example`
- `composer.json`
- `package.json`
- `vite.config.js`
- `resources/js/` com páginas, componentes e bootstrap do Inertia
- `app/` com Models, Controllers, Services, Repositories, Requests, Actions e Exceptions
- `database/migrations/` com tabelas de `employees`, `services`, `clients` e `appointments`
- `database/seeders/DatabaseSeeder.php`
- `database/factories/` com factories básicas
- `routes/web.php` e `routes/auth.php`

## Como usar

1. Copie `.env.example` para `.env`.
2. Suba os containers:

```bash
docker compose up -d --build
```

3. Instale as dependências PHP:

```bash
docker compose exec app composer install
```

4. Instale as dependências JS:

```bash
docker compose exec node yarn install
```

5. Gere a chave da aplicação e rode as migrations:

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

6. Acesse o app em `http://localhost:8080`.

## Testes e TDD

O projeto já inclui um setup inicial para testes com PHPUnit e Orchestra Testbench, com cobertura de:

- testes unitários em `tests/Unit`
- testes de serviço/integração em `tests/Feature`

Para rodar os testes localmente:

```bash
docker compose exec app composer test:ci
```

## Pipeline de CI

Há uma pipeline GitHub Actions em `.github/workflows/ci.yml` que dispara em `push` e `pull_request` na branch `main`.
Ela instala dependências, prepara o ambiente e executa os testes antes de qualquer deploy.

## Observação

O scaffold foi gerado a partir do arquivo `PROJETO_SALAO_AGENDAMENTO.md`. Ainda pode exigir ajustes finos antes de funcionar como um projeto Laravel completo, mas a estrutura principal já está criada.
