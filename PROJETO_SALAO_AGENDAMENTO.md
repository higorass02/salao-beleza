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
│   │       ├── CreateAppointmentAction.php
│   │       └── UpdateAppointmentAction.php   # valida conflito excluindo o próprio registro
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── EmployeeController.php
│   │   │   ├── ServiceController.php
│   │   │   ├── ClientController.php
│   │   │   ├── AppointmentController.php
│   │   │   └── Controller.php
│   │   ├── Middleware/
│   │   │   └── HandleInertiaRequests.php     # compartilha auth.user.is_admin via Inertia
│   │   └── Requests/
│   │       ├── StoreEmployeeRequest.php
│   │       ├── UpdateEmployeeRequest.php
│   │       ├── StoreServiceRequest.php
│   │       ├── UpdateServiceRequest.php
│   │       ├── StoreClientRequest.php
│   │       ├── UpdateClientRequest.php
│   │       ├── StoreAppointmentRequest.php
│   │       └── UpdateAppointmentRequest.php  # inclui campo status
│   ├── Models/
│   │   ├── User.php            # campo is_admin (boolean) — controla acesso a edição
│   │   ├── Employee.php
│   │   ├── Service.php
│   │   ├── Client.php
│   │   └── Appointment.php     # casts datetime:Y-m-d H:i:s (preserva timezone local)
│   ├── Repositories/
│   │   ├── Contracts/
│   │   │   └── AppointmentRepositoryInterface.php
│   │   └── AppointmentRepository.php         # hasConflict aceita ?int $excludeId
│   ├── Services/
│   │   ├── EmployeeService.php
│   │   ├── ServiceService.php
│   │   ├── ClientService.php
│   │   └── AppointmentService.php            # métodos: create, update, listUpcoming, listForCalendar
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
│           ├── Appointments/
│           │   └── Edit.vue                  # edição de agendamento (apenas admin)
│           └── Calendar/
│               ├── Index.vue                 # calendário estilo Google Agenda
│               ├── AppointmentModal.vue      # criação via clique no calendário
│               └── AppointmentDetailModal.vue # detalhes + botão Editar (admin)
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

### Tabela `users` (usuários internos)
| Coluna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| name | string | |
| email | string unique | |
| **is_admin** | boolean | default false — controla acesso à edição de agendamentos |
| password | string | hashed |
| timestamps | | |

> O seeder `AdminUserSeeder` cria/mantém o usuário `admin@salao.com` com `is_admin = true`. O campo `is_admin` é compartilhado via `HandleInertiaRequests` em `auth.user.is_admin` para todos os componentes Vue.

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

> **Cast importante:** `starts_at` e `ends_at` usam cast `datetime:Y-m-d H:i:s` no model. Isso serializa no formato `"YYYY-MM-DD HH:mm:ss"` respeitando o `APP_TIMEZONE` (`America/Sao_Paulo`), evitando que o JSON converta para UTC e quebre a exibição na tela de edição.
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
- Calendário em visão **semana/dia** usando **FullCalendar** (`@fullcalendar/vue3`).
- Clicar em um **slot vazio** abre `AppointmentModal.vue` (criação).
- Clicar em um **evento existente** abre `AppointmentDetailModal.vue` (detalhes).
- Eventos carregados via `AppointmentRepository::getForCalendar()` com janela de **4 semanas atrás → 8 semanas à frente** (evita filtro `>= now()` que ocultava agendamentos do dia).
- **Cada prestador tem uma cor fixa** (paleta de 8 cores) — os eventos e os pills de filtro compartilham a mesma cor.
- Arrastar/redimensionar evento (opcional, fase 2) → revalida conflito.

### Filtros do calendário (`Calendar/Index.vue`)
Barra de filtros acima do calendário com dois controles acumulativos (ambos podem estar ativos ao mesmo tempo):

| Controle | Posição | Comportamento |
|---|---|---|
| **Busca de cliente** | Esquerda | Autocomplete com debounce 300ms — consulta `GET /clients/search?q=`. Exibe nome + telefone/e-mail nas sugestões. Botão X para limpar. Ao selecionar um cliente, filtra o calendário por `client_id`. |
| **Pills de prestador** | Direita | Um pill por funcionário ativo + "Todos". Pill ativo fica com a cor do prestador. Ao clicar, filtra por `employee_id`. |

Filtragem é feita **no frontend** (`getFiltered()`), atualizando o FullCalendar via `removeAllEvents()` + `addEventSource()` — sem round-trip ao servidor.

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

> 💡 A busca de clientes usa a rota leve `GET /clients/search?q=` que retorna JSON simples — sem API REST completa.

### Modal de detalhes `AppointmentDetailModal.vue`
Aberto ao clicar num evento do calendário. Exibe: status (badge colorido), horário início–fim, cliente (nome + telefone), serviço (nome, duração, preço), funcionário (nome, cargo) e observações. Footer com botão **Editar** visível apenas quando `auth.user.is_admin === true`.

### Tela de edição `Appointments/Edit.vue`
Acessada via botão Editar do `AppointmentDetailModal`. Formulário pré-preenchido com todos os campos do agendamento original. Campos editáveis: data, horário, serviço, funcionário, cliente (com busca), status (`scheduled` / `done` / `canceled`) e observações. Ao salvar, executa `UpdateAppointmentAction` que recalcula `ends_at` e valida conflito de horário **excluindo o próprio agendamento** da checagem (`hasConflict(..., $excludeId)`). Redireciona para a agenda após sucesso.

---

## 🐳 Ambiente Docker

### Serviços do `docker-compose.yml`
| Serviço | Imagem | Porta | Função |
|---|---|---|---|
| `app` | PHP 8.3-fpm (Dockerfile custom) | — | Roda Laravel |
| `nginx` | nginx:alpine | `8080:80` | Servidor web |
| `mysql` | mysql:8.0 | `3306:3306` | Banco de dados |
| `phpmyadmin` | phpmyadmin:latest | `8081:80` | Admin do MySQL |
| `node` | node:24-alpine | `5173:5173` | Vite dev server (opcional) |

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
| 2026-06-08 | Atualização de infra: Node.js 20 → 24 no Docker e CI. |
| 2026-06-08 | Fix calendário: substituído `getUpcoming()` (>= now) por `getForCalendar($from, $to)` com janela de datas — agendamentos do dia não desaparecem mais. |
| 2026-06-08 | Novo: `AppointmentDetailModal.vue` — clique no evento do calendário abre detalhes. |
| 2026-06-08 | Novo: filtro por prestador no calendário — pills coloridos, cores distintas por funcionário nos eventos. |
| 2026-06-08 | Novo: busca de cliente com autocomplete no calendário — alinhada à esquerda, filtro acumulativo com prestador. |
| 2026-06-08 | Novo: edição de agendamento — `UpdateAppointmentAction`, `UpdateAppointmentRequest`, `Appointments/Edit.vue`, rotas `edit`/`update`. Botão Editar visível apenas para admins (`is_admin`). |
| 2026-06-08 | Fix serialização: cast `datetime:Y-m-d H:i:s` no model `Appointment` para preservar horário local (`America/Sao_Paulo`) no JSON em vez de converter para UTC. |
