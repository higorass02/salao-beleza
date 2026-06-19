# Salão Espaço Delas — Sistema de Agendamento

> Sistema web interno para gestão de agendamentos, clientes e financeiro de um salão de beleza. Em produção em [salao.higordev.com.br](https://salao.higordev.com.br).

**Stack:** Laravel 11 · Vue 3 · Inertia.js · MySQL 8 · Tailwind CSS · Docker Compose

---

## Sobre o Projeto

Aplicação full-stack desenvolvida do zero para automatizar a operação diária de um salão de beleza real. O sistema eliminou o controle manual em cadernos e planilhas, centralizando agendamentos, gestão financeira e comunicação com colaboradores em uma única plataforma web.

O projeto foi construído com arquitetura **MVCS** (Model · View · Controller · Service · Repository · Action), separando responsabilidades de forma clara e garantindo testabilidade em todas as camadas.

---

## Funcionalidades

### Autenticação & Controle de Acesso
- Dois papéis independentes: **Administrador** e **Colaborador**
- Redirect pós-login automático por papel (admin → `/dashboard`, colaborador → `/collaborator`)
- Troca de senha obrigatória no primeiro acesso com middleware dedicado
- Recuperação de senha via e-mail (Laravel Breeze + Mailhog em dev)
- Middleware chain: `EnsureIsAdmin` · `EnsureIsCollaborator` · `EnsurePasswordChanged`

### Agendamentos
- Calendário interativo com **FullCalendar v6** (visualizações dia/semana/mês)
- Filtro por prestador e busca de cliente com **autocomplete** em tempo real
- Criação, edição e cancelamento com validação de conflito de horários
- Admin pode editar qualquer agendamento; colaborador só gerencia os próprios
- Modal de detalhes rápidos diretamente no calendário

### Clientes
- CRUD completo com busca por nome e telefone
- Acessível a admins e colaboradores (permissão compartilhada)
- Autocomplete inteligente: exibe apelido → telefone → nome conforme disponibilidade

### Colaboradores (Employees)
- CRUD com vinculação automática de conta de usuário
- Ao cadastrar, gera senha temporária e envia credenciais por e-mail
- Flag por colaborador para ativar/desativar notificações diárias

### Fechamento de Caixa
- **Diário:** totais por prestador com taxa de casa aplicada
- **Por prestador:** fechamento individual com cálculo do valor a receber
- **Semanal:** visão consolidada com `provider_total` e `store_total` para cada colaborador

### Notificações por E-mail
- Cron às **09h** dispara `appointments:notify-daily`
- Cada colaborador recebe um resumo dos agendamentos do dia
- **Dois níveis de controle:** admin habilita/desabilita por funcionário; colaborador controla para si mesmo em `/collaborator/settings`
- Envio condicional: só dispara dentro do horário de funcionamento e se houver agendamentos com status `scheduled`

### Área do Colaborador
- Dashboard com contadores de hoje/semana e próximos agendamentos
- Calendário filtrado apenas para os agendamentos do próprio colaborador
- CRUD de agendamentos e clientes com proteção de ownership
- Configurações pessoais (preferências de notificação, troca de senha)

### Configurações Globais
- Taxa de casa (`house_fee_rate`) aplicada nos fechamentos
- Horário de funcionamento do estabelecimento (usado nas notificações)

### Landing Page Pública
- Página institucional com hero, serviços, galeria e link para área admin
- Sem autenticação — acessível a qualquer visitante

---

## Arquitetura

```
app/
├── Actions/          # Operações complexas e atômicas (CreateAppointmentAction, UpdateAppointmentAction)
├── Console/Commands/ # Artisan commands agendados (SendDailyAppointmentNotifications)
├── Http/
│   ├── Controllers/  # Magros — apenas orquestração de request/response
│   ├── Middleware/   # EnsureIsAdmin, EnsureIsCollaborator, EnsurePasswordChanged
│   └── Requests/     # Form Requests com validação centralizada
├── Models/           # Eloquent com casts e relacionamentos
├── Notifications/    # DailyAppointmentsNotification, WelcomeEmployeeNotification
├── Repositories/     # Acesso a dados isolado (AppointmentRepository, etc.)
└── Services/         # Regras de negócio (CashClosingService, EmployeeService, etc.)
```

**Decisões técnicas relevantes:**
- `Appointment` usa `cast datetime:Y-m-d H:i:s` para preservar timezone `America/Sao_Paulo` sem conversão UTC no JSON
- `UpdateAppointmentAction` exclui o próprio agendamento na checagem de conflito (`excludeId`) — evita falso positivo ao editar
- `WeeklyClosingController` deriva totais como valores **esperados** (total − taxa, taxa) em vez de usar campo `paid_to`
- Inertia.js elimina a necessidade de uma API REST separada, mantendo tipagem compartilhada entre backend e frontend
- `public/build/` commitado no git — Hostinger shared hosting não tem Node.js

---

## CI/CD & Deploy

| Ambiente | Descrição |
|---|---|
| **Desenvolvimento** | Docker Compose (PHP-FPM, Nginx, MySQL, Node, Mailhog) |
| **CI** | GitHub Actions — instala deps, roda migrations e executa a suite de testes a cada push/PR |
| **Produção** | Hostinger shared hosting (PHP 8.3) via `deploy-hostinger.sh` |

**Fluxo de deploy:**
```bash
# 1. Build local (Hostinger não tem Node.js)
docker compose run --rm node yarn build

# 2. Commit e push do build
git add public/build && git commit -m "build" && git push

# 3. No servidor via SSH
git pull && bash ~/salao-beleza/deploy-hostinger.sh
# script: copia assets, ajusta paths, roda config:cache e route:cache
```

---

## Testes

Suite de testes com **PHPUnit** usando banco **SQLite in-memory** (sem Docker em CI):

```bash
docker compose exec app composer test:ci
```

- **Unit:** Services e Actions com mocks de repository
- **Feature:** Controllers via HTTP com banco real (SQLite)
- **Padrão:** sem mocks de banco em Feature tests — integração real para detectar divergências entre model e migration

---

## Setup Local

```bash
# 1. Suba os containers
docker compose up -d --build

# 2. Instale dependências
docker compose exec app composer install
docker compose exec node yarn install

# 3. Configure o ambiente
docker compose exec app cp .env.example .env
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed

# 4. Compile os assets
docker compose exec node yarn dev

# 5. Acesse em http://localhost:8080
```

**Credenciais padrão após seed:**
- Admin: `admin@salao.com` / `password`

---

## Stack Completa

| Camada | Tecnologia |
|---|---|
| Backend | Laravel 11 (PHP 8.3) |
| Frontend | Vue 3 + Inertia.js |
| Estilo | Tailwind CSS v3 |
| Calendário | FullCalendar v6 |
| Banco de dados | MySQL 8 |
| Dev environment | Docker Compose |
| CI | GitHub Actions |
| Hosting | Hostinger (shared, PHP 8.3) |
| E-mail (dev) | Mailhog |
