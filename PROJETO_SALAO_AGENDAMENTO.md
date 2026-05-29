# 📋 Projeto: Sistema de Agendamento para Salão de Beleza

> **Documento mestre do projeto.** Este arquivo descreve a arquitetura, os padrões e os comandos de criação. O assistente (Claude) deve ler este arquivo antes de gerar qualquer código.

---

## 🎯 Sumário

- [Visão Geral](#-visão-geral)
- [Stack e Versões](#-stack-e-versões)
- [Arquitetura MVCS](#-arquitetura-mvcs)
- [Design Patterns Adotados](#-design-patterns-adotados)
- [Estrutura de Pastas](#-estrutura-de-pastas)
- [Modelagem de Dados](#-modelagem-de-dados)
- [Tela de Agendamento (estilo Google Agenda)](#-tela-de-agendamento-estilo-google-agenda)
- [Ambiente Docker](#-ambiente-docker)
- [Scripts Yarn / NPM](#-scripts-yarn--npm)
- [Roadmap de Criação (Passo a Passo)](#-roadmap-de-criação-passo-a-passo)
- [Comandos Rápidos para o Assistente](#-comandos-rápidos-para-o-assistente)
- [Convenções e Padrões de Código](#-convenções-e-padrões-de-código)
- [Changelog do Documento](#-changelog-do-documento)

---

## 🔎 Visão Geral

Sistema **interno** (uso por funcionários do salão, não pelo cliente final) para:

1. **Administração básica (CRUD)**
   - Funcionários
   - Serviços
   - Clientes
2. **Agendamento avançado**
   - Calendário com usabilidade inspirada no **Google Agenda**
   - Modal de criação ao clicar num dia/horário
   - Validação de conflito de horário por profissional + serviço

O projeto deve ser **simples**, mas aplicar **alguns** padrões de projeto bem escolhidos — sem over-engineering.

### Decisões definidas pelo usuário

| Tema | Decisão | Observação |
|---|---|---|
| Stack | **Laravel + Vue 3** com **Breeze (stack Vue/Inertia)** | Breeze entrega login pronto e integra Vue 3 nativamente; mais simples que Sanctum puro para um admin interno. |
| Autenticação | **Laravel Breeze** | Telas de login/registro geradas automaticamente. Acesso restrito a usuários internos. |
| Duração de serviço | **Definida na tela de cadastro de Serviço** (campo `duration_minutes`) | Não é fixa no código nem escolhida no agendamento — é uma propriedade do serviço, editável pelo admin no CRUD de Serviços. |
| API REST | **Fora do escopo atual** — foco total no admin web | A arquitetura (Services/Resources) já deixa a base pronta para uma API futura, mas ela não será construída agora. |

> 💡 **Sobre Inertia vs API:** com Breeze stack Vue, o frontend usa **Inertia.js** — o Laravel entrega as páginas Vue diretamente, sem necessidade de uma API REST separada nem de Axios para navegação. Os componentes continuam em Vue 3 com `<script setup>`. Quando o app mobile for desenvolvido no futuro, uma camada de API REST será adicionada reaproveitando os Services.

---

## 🧱 Stack e Versões

| Camada | Tecnologia | Versão alvo |
|---|---|---|
| Backend | **Laravel** | versão estável mais atual |
| Linguagem | PHP | 8.3+ |
| Frontend | **Vue 3** (`<script setup>`) | 3.4+ |
| Ponte back↔front | **Inertia.js** | 2+ |
| Build/Bundler | Vite | 5+ |
| Gerenciador de pacotes JS | **Yarn** | 1.22+ (Classic) |
| Banco de dados | **MySQL** | 8.0 |
| Admin de BD | **phpMyAdmin** | latest |
| Containerização | **Docker + Docker Compose** | v2 |
| Autenticação / scaffold | **Laravel Breeze** (stack Vue) | — |
| UI Calendar | FullCalendar (`@fullcalendar/vue3`) | 6+ |
| Estado (frontend) | Pinia *(opcional, só se necessário)* | 2+ |

> ⚠️ Antes de gerar, o assistente deve **verificar a versão estável mais atual do Laravel** e ajustar este quadro se necessário.

---

## 🏛 Arquitetura MVCS

O projeto segue o padrão **MVCS** (Model–View–Controller–Service):

```
Requisição HTTP
      │
      ▼
   [Controller]  ──► valida input (via Form Request) e orquestra
      │
      ▼
   [Service]     ──► concentra TODA a regra de negócio
      │
      ▼
[Repository]*    ──► acesso a dados (opcional, ver Design Patterns)
      │
      ▼
   [Model]       ──► Eloquent, relacionamentos, scopes
      │
      ▼
   [View]        ──► página Vue renderizada via Inertia::render()
```

**Princípio central:** o **Controller é magro**. Ele nunca contém regra de negócio — apenas recebe o request, chama o Service e devolve a resposta. Toda lógica (validação de conflito de agendamento, cálculos, etc.) vive nos **Services**.

### Responsabilidades

| Camada | Responsabilidade | NÃO faz |
|---|---|---|
| **Model** | Mapeamento Eloquent, relações, casts, scopes | Regra de negócio complexa |
| **View** | Apresentação (componentes Vue via Inertia) | Lógica de domínio |
| **Controller** | Receber request, delegar ao Service, retornar `Inertia::render()` | Regra de negócio, acesso direto ao BD |
| **Service** | Regra de negócio, transações, orquestração | Apresentação |

---

## 🎨 Design Patterns Adotados

Apenas padrões que **agregam valor** sem complexidade desnecessária:

| Padrão | Onde usar | Motivo |
|---|---|---|
| **Service Layer** | `app/Services/*` | Centraliza regra de negócio (núcleo do MVCS) |
| **Repository Pattern** | `app/Repositories/*` | Abstrai acesso a dados; facilita testes. *Aplicar só onde houver query reaproveitável.* |
| **Form Request** | `app/Http/Requests/*` | Validação isolada do controller |
| **DTO / Data object** *(opcional)* | `app/Data/*` | Transporta dados validados do Controller ao Service de forma tipada |
| **Action / Single-purpose class** | `app/Actions/*` | Para operações isoladas e complexas (ex: `CreateAppointmentAction`) |
| **Strategy** *(opcional)* | Validação de conflito de agendamento | Permite trocar a regra de conflito no futuro |
| **Observer** *(opcional)* | `app/Observers/*` | Eventos do ciclo de vida do Model (ex: log de criação de agendamento) |

> 🧠 **Regra de ouro:** não criar abstração sem necessidade. Repository, Strategy e DTO só entram se houver ganho real. Para CRUDs simples (funcionário, serviço, cliente), Service direto sobre Eloquent é suficiente.
>
> 📦 **API Resources:** não são usados agora (Inertia entrega os dados direto às páginas Vue). Ficam reservados para quando a API REST do app futuro for criada.

---

## 📁 Estrutura de Pastas

```
salao-agendamento/
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   └── php/
│       └── Dockerfile
├── docker-compose.yml
├── .env.example
│
├── app/
│   ├── Actions/
│   │   └── Appointment/
│   │       └── CreateAppointmentAction.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── EmployeeController.php
│   │   │   ├── ServiceController.php
│   │   │   ├── ClientController.php
│   │   │   ├── AppointmentController.php
│   │   │   └── Controller.php
│   │   ├── Middleware/
│   │   │   └── HandleInertiaRequests.php
│   │   └── Requests/
│   │       ├── Employee/{Store,Update}EmployeeRequest.php
│   │       ├── Service/{Store,Update}ServiceRequest.php
│   │       ├── Client/{Store,Update}ClientRequest.php
│   │       └── Appointment/{Store,Update}AppointmentRequest.php
│   ├── Models/
│   │   ├── User.php            # usuários internos (admin) — vem do Breeze
│   │   ├── Employee.php
│   │   ├── Service.php
│   │   ├── Client.php
│   │   └── Appointment.php
│   ├── Repositories/
│   │   ├── Contracts/
│   │   │   └── AppointmentRepositoryInterface.php
│   │   └── AppointmentRepository.php
│   ├── Services/
│   │   ├── EmployeeService.php
│   │   ├── ServiceService.php
│   │   ├── ClientService.php
│   │   └── AppointmentService.php
│   └── Exceptions/
│       └── AppointmentConflictException.php
│
├── resources/
│   └── js/
│       ├── app.js              # bootstrap do Inertia + Vue
│       ├── Layouts/
│       │   └── AdminLayout.vue
│       ├── Components/
│       │   ├── BaseModal.vue
│       │   └── ClientSearchInput.vue
│       └── Pages/              # cada página = uma rota Inertia
│           ├── Dashboard.vue
│           ├── Auth/           # telas geradas pelo Breeze (login etc.)
│           ├── Employees/
│           │   ├── Index.vue
│           │   ├── Create.vue
│           │   └── Edit.vue
│           ├── Services/
│           │   ├── Index.vue
│           │   ├── Create.vue
│           │   └── Edit.vue
│           ├── Clients/
│           │   ├── Index.vue
│           │   ├── Create.vue
│           │   └── Edit.vue
│           └── Calendar/
│               ├── Index.vue          # calendário estilo Google Agenda
│               └── AppointmentModal.vue
│
├── routes/
│   ├── web.php                 # todas as rotas (Inertia)
│   └── auth.php                # rotas de login (Breeze)
│
└── database/
    ├── migrations/
    ├── factories/
    └── seeders/
```

> ℹ️ Não há pasta `Controllers/Api` nem `Resources/` neste momento — o foco é o admin web via Inertia. Essas pastas serão adicionadas quando a API do app futuro entrar no escopo.

---

## 🗄 Modelagem de Dados

### Tabela `employees` (funcionários)
| Coluna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| name | string | |
| email | string unique | |
| phone | string nullable | |
| role | string | ex: cabeleireiro, manicure |
| active | boolean | default true |
| timestamps | | |

### Tabela `services` (serviços)
| Coluna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| name | string | |
| description | text nullable | |
| price | decimal(10,2) | |
| **duration_minutes** | unsignedInteger | **Definido pelo admin na tela de cadastro/edição de Serviço.** É a duração padrão do serviço. |
| active | boolean | default true |
| timestamps | | |

### Tabela `clients` (clientes)
| Coluna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| name | string | indexado (busca) |
| email | string nullable | indexado (busca) |
| phone | string nullable | indexado (busca) |
| notes | text nullable | |
| timestamps | | |

### Tabela `appointments` (agendamentos)
| Coluna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| client_id | FK → clients | |
| employee_id | FK → employees | |
| service_id | FK → services | |
| starts_at | datetime | data + horário de início |
| ends_at | datetime | calculado: `starts_at + duration_minutes` |
| status | enum | `scheduled`, `done`, `canceled` (default `scheduled`) |
| notes | text nullable | |
| timestamps | | |

**Índice composto recomendado:** `(employee_id, service_id, starts_at)` para acelerar a checagem de conflito.

### Relacionamentos
- `Employee` hasMany `Appointment`
- `Service` hasMany `Appointment`
- `Client` hasMany `Appointment`
- `Appointment` belongsTo `Client`, `Employee`, `Service`

---

## 🗓 Tela de Agendamento (estilo Google Agenda)

### Comportamento
- Calendário em visão **semana/dia/mês** usando **FullCalendar** (`@fullcalendar/vue3`).
- Clicar em um **dia ou slot de horário** abre o componente `AppointmentModal.vue`.
- Eventos existentes aparecem no calendário coloridos por funcionário.
- Arrastar/redimensionar evento (opcional, fase 2) → revalida RN-01.

### Campos do modal `AppointmentModal.vue`
| Campo | Componente | Comportamento |
|---|---|---|
| Cliente | `ClientSearchInput.vue` | Buscador que filtra por **nome, e-mail ou telefone** (debounce, consulta a rota de busca de clientes) |
| Serviço | `<select>` | Lista de serviços ativos pré-cadastrados |
| Funcionário | `<select>` | Lista de funcionários ativos |
| Data | `<input type="date">` | Pré-preenchido com o dia clicado |
| Horário | `<input type="time">` | Pré-preenchido com o slot clicado (se houver) |

> A duração não aparece no modal — ela vem do serviço selecionado e é usada no backend para calcular `ends_at`.

### Fluxo de submissão (via Inertia)
1. Vue monta `starts_at` (data + horário) e envia o form com `useForm().post('/appointments')`.
2. `StoreAppointmentRequest` valida formato, serviço/funcionário ativos e data futura.
3. `AppointmentController::store()` chama `AppointmentService::create()`.
4. Service calcula `ends_at` (usando `duration_minutes` do serviço) e checa conflito de horário.
5. Conflito → `AppointmentConflictException` vira erro de validação 422 → Inertia popula `errors` → modal exibe a mensagem inline.
6. Sucesso → Inertia recarrega os eventos do calendário (props da página) e fecha o modal.

> 💡 A busca de clientes do `ClientSearchInput` pode usar uma rota leve (`clients.search`) que retorna JSON simples, ou o helper de partial reload do Inertia. Não exige uma API REST completa.

---

## 🐳 Ambiente Docker

### Serviços do `docker-compose.yml`
| Serviço | Imagem | Porta | Função |
|---|---|---|---|
| `app` | PHP 8.3-fpm (Dockerfile custom) | — | Roda Laravel |
| `nginx` | nginx:alpine | `8080:80` | Servidor web |
| `mysql` | mysql:8.0 | `3306:3306` | Banco de dados |
| `phpmyadmin` | phpmyadmin:latest | `8081:80` | Admin do MySQL |
| `node` | node:20-alpine | `5173:5173` | Vite dev server (opcional) |

### Variáveis de ambiente (`.env`)
```
DB_CONNECTION=mysql
DB_HOST=mysql          # nome do serviço, não localhost
DB_PORT=3306
DB_DATABASE=salao
DB_USERNAME=salao
DB_PASSWORD=secret
```

### Acesso
- App: `http://localhost:8080`
- phpMyAdmin: `http://localhost:8081` (login: `salao` / `secret`)
- Vite HMR: `http://localhost:5173`

### Subir o ambiente
```bash
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app yarn install
```

---

## 🧹 Scripts Yarn / NPM

No `package.json`, configurar um `yarn dev` que **limpa todos os caches** (back e front) antes de subir o Vite. Sugestão:

```jsonc
{
  "scripts": {
    "clear:laravel": "php artisan optimize:clear && php artisan view:clear && php artisan route:clear && php artisan config:clear && php artisan cache:clear",
    "clear:front": "rm -rf node_modules/.vite public/build && rm -rf storage/framework/views/*",
    "predev": "yarn clear:laravel && yarn clear:front",
    "dev": "vite",
    "build": "vite build"
  }
}
```

> Com isso, `yarn dev` automaticamente roda `predev` (limpeza completa) antes de iniciar o servidor de desenvolvimento.
> Dentro do Docker: `docker compose exec node yarn dev` (ou rodar no host se o Node estiver instalado).

---

## 🔮 Preparação para o App Futuro (fora do escopo atual)

> A API REST **não será construída agora** — o foco é o admin web via Inertia. Esta seção apenas registra como o projeto fica preparado para isso.

Como toda a regra de negócio está nos **Services** (e não nos Controllers), uma futura API mobile poderá:

1. Adicionar `app/Http/Controllers/Api/*` que **reutilizam os mesmos Services**.
2. Adicionar `app/Http/Resources/*` para padronizar a saída JSON.
3. Instalar **Laravel Sanctum** para autenticação por token.
4. Criar `routes/api.php` com os endpoints.

Nenhuma regra de negócio precisará ser reescrita — só será exposta por um novo "canal". Por isso o documento insiste em manter o **Controller magro** e a lógica concentrada no Service.

---

## 🚀 Roadmap de Criação (Passo a Passo)

Ordem recomendada para o assistente executar a criação do projeto:

1. **Scaffold Laravel** — criar projeto Laravel na versão estável mais atual.
2. **Docker** — criar `docker-compose.yml`, `Dockerfile`, config do nginx; subir containers.
3. **Banco** — configurar `.env`, validar conexão MySQL + phpMyAdmin.
4. **Auth / scaffold** — instalar **Laravel Breeze com stack Vue** (gera login + Inertia + Vue 3).
5. **Migrations** — criar as 4 tabelas + índices (seção Modelagem).
6. **Models** — criar Models com relacionamentos, casts e scopes.
7. **Factories + Seeders** — dados de teste para funcionários, serviços, clientes.
8. **Camada MVCS** — Form Requests → Services → Controllers (com `Inertia::render()`).
9. **Repository** — apenas `AppointmentRepository` (queries de conflito/calendário).
10. **Action** — `CreateAppointmentAction` encapsulando a lógica de criação e conflito.
11. **Exception** — `AppointmentConflictException` + tratamento como erro de validação 422.
12. **Rotas** — registrar tudo em `routes/web.php` (rotas Inertia, protegidas por auth).
13. **Frontend base** — instalar FullCalendar via Yarn; configurar layout admin.
14. **Páginas Inertia** — `Dashboard` + páginas de CRUD (Index/Create/Edit de cada recurso).
15. **Calendário** — `Calendar/Index.vue` + `AppointmentModal.vue` + `ClientSearchInput.vue`.
16. **Scripts Yarn** — configurar `predev`/`dev` com limpeza de cache.
17. **Testes** — feature tests cobrindo conflito de agendamento, cálculo de término e validações.
18. **README** — instruções de setup para novos devs.

---

## 🤖 Comandos Rápidos para o Assistente

Frases curtas que você pode usar comigo (Claude) para avançar no projeto. Cada uma assume que este `.md` já foi lido como referência.

| Comando | O que faço |
|---|---|
| `"Inicie o projeto"` | Executo os passos 1–4 do Roadmap (scaffold + Docker + banco + auth). |
| `"Crie a camada de dados"` | Passos 5–7 (migrations, models, factories, seeders). |
| `"Crie o backend MVCS"` | Passos 8–12 (requests, resources, services, controllers, rotas). |
| `"Crie o frontend base"` | Passos 13–14 (Vue, router, CRUDs). |
| `"Crie a tela de agendamento"` | Passo 15 (calendário + modal + busca de cliente). |
| `"Finalize"` | Passos 16–18 (scripts, testes, README). |
| `"Atualize o documento"` | Reviso este `.md` com o estado atual do projeto. |

---

## 📏 Convenções e Padrões de Código

### Backend (PHP/Laravel)
- PSR-12; rodar **Laravel Pint** antes de commit.
- Controllers: máximo ~5 linhas por método (apenas delega).
- Nomes: `EmployeeService`, `StoreEmployeeRequest`, `EmployeeResource`.
- Sem regra de negócio em Controllers ou Models.
- Toda escrita que toca múltiplas tabelas → dentro de `DB::transaction()`.
- Exceptions de domínio em `app/Exceptions/`.

### Frontend (Vue 3 + Inertia)
- **Sempre** `<script setup>`.
- Componentes em `PascalCase`; um componente por arquivo.
- Páginas em `resources/js/Pages/` (cada uma corresponde a uma rota Inertia).
- Navegação e formulários usam os helpers do Inertia (`Link`, `useForm`, `router`).
- Pinia só se houver estado realmente compartilhado entre páginas; caso contrário, props do Inertia + `ref`/`reactive` bastam.
- Componentes "burros" (apresentação) separados de páginas (lógica).

### Git
- Branches: `feature/`, `fix/`, `chore/`.
- Commits no padrão Conventional Commits (`feat:`, `fix:`, `docs:`...).

---

## 📝 Changelog do Documento

> Registre aqui toda alteração relevante neste `.md`. Formato: data — descrição.

| Data | Alteração |
|---|---|
| 2026-05-27 | Criação do documento mestre: stack, MVCS, design patterns, modelagem, RN-01 a RN-05, Docker, roadmap. |
| 2026-05-27 | Definições do usuário aplicadas: autenticação via **Laravel Breeze (stack Vue/Inertia)** no lugar de Sanctum; API REST tirada do escopo atual (foco no admin web); duração do serviço definida na tela de cadastro pelo admin. Estrutura de pastas, rotas e roadmap reescritos para o padrão Inertia. |
| 2026-05-29 | Seção "Regras de Negócio" removida do documento. |
