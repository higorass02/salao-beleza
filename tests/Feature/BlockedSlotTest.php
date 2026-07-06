<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\BlockedSlotController as AdminBlockedSlotController;
use App\Http\Controllers\Collaborator\BlockedSlotController as CollaboratorBlockedSlotController;
use App\Models\Appointment;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Tests\TestCase;

/**
 * Testa criação, listagem e remoção de bloqueios de horários.
 * Cobre: admin pode bloquear qualquer funcionário; colaborador só o próprio.
 */
class BlockedSlotTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(str_repeat('a', 32)));
        $app['config']->set('auth.providers.users.model', \App\Models\User::class);
        $app['config']->set('auth.guards.web.provider', 'users');
        $app['router']->aliasMiddleware('admin',            \App\Http\Middleware\EnsureIsAdmin::class);
        $app['router']->aliasMiddleware('collaborator',     \App\Http\Middleware\EnsureIsCollaborator::class);
        $app['router']->aliasMiddleware('password.changed', \App\Http\Middleware\EnsurePasswordChanged::class);
    }

    protected function defineRoutes($router): void
    {
        $bindings = \Illuminate\Routing\Middleware\SubstituteBindings::class;

        // Admin
        $router->middleware(['auth', 'admin', $bindings])->group(function () use ($router) {
            $router->get('/admin/blocked-slots', [AdminBlockedSlotController::class, 'index'])
                   ->name('admin.blocked-slots.index');
            $router->post('/admin/blocked-slots', [AdminBlockedSlotController::class, 'store'])
                   ->name('admin.blocked-slots.store');
            $router->delete('/admin/blocked-slots/{slot}', [AdminBlockedSlotController::class, 'destroy'])
                   ->name('admin.blocked-slots.destroy');
            $router->get('/admin/blocked-slots/appointments-in-range', [AdminBlockedSlotController::class, 'appointmentsInRange'])
                   ->name('admin.blocked-slots.appointments-in-range');
        });

        // Colaborador
        $router->middleware(['auth', 'collaborator', $bindings])->group(function () use ($router) {
            $router->get('/collaborator/blocked-slots', [CollaboratorBlockedSlotController::class, 'index'])
                   ->name('collaborator.blocked-slots.index');
            $router->post('/collaborator/blocked-slots', [CollaboratorBlockedSlotController::class, 'store'])
                   ->name('collaborator.blocked-slots.store');
            $router->delete('/collaborator/blocked-slots/{slot}', [CollaboratorBlockedSlotController::class, 'destroy'])
                   ->name('collaborator.blocked-slots.destroy');
        });

        // Rotas dummy para redirect
        $router->get('/dashboard', fn () => response()->noContent())->name('dashboard');
        $router->get('/collaborator', fn () => response()->noContent())->name('collaborator.dashboard');
        $router->get('/login', fn () => response()->noContent())->name('login');
    }

    protected function resolveApplicationExceptionHandler($app): void
    {
        $app->bind(ExceptionHandler::class, \App\Exceptions\Handler::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_admin' => true, 'must_change_password' => false]);
    }

    private function makeCollaborator(Employee $employee): User
    {
        return User::factory()->create([
            'is_admin'            => false,
            'employee_id'         => $employee->id,
            'must_change_password' => false,
        ]);
    }

    private function makeEmployee(): Employee
    {
        return Employee::factory()->create(['active' => true]);
    }

    private function blockPayload(Employee $employee, array $overrides = []): array
    {
        return array_merge([
            'employee_id' => $employee->id,
            'starts_at'   => '2099-12-01 10:00',
            'ends_at'     => '2099-12-01 12:00',
            'notes'       => 'Folga',
        ], $overrides);
    }

    // ── Admin: criar bloqueio ─────────────────────────────────────────────────

    public function test_admin_can_create_blocked_slot(): void
    {
        $employee = $this->makeEmployee();
        $admin    = $this->makeAdmin();

        $this->actingAs($admin)
             ->post('/admin/blocked-slots', $this->blockPayload($employee))
             ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'employee_id' => $employee->id,
            'status'      => 'blocked',
            'client_id'   => null,
            'service_id'  => null,
        ]);
    }

    public function test_admin_blocked_slot_has_correct_times(): void
    {
        $employee = $this->makeEmployee();

        $this->actingAs($this->makeAdmin())
             ->post('/admin/blocked-slots', $this->blockPayload($employee, [
                 'starts_at' => '2099-12-01 08:00',
                 'ends_at'   => '2099-12-01 09:00',
             ]));

        $this->assertDatabaseHas('appointments', [
            'employee_id' => $employee->id,
            'status'      => 'blocked',
            'starts_at'   => '2099-12-01 08:00:00',
            'ends_at'     => '2099-12-01 09:00:00',
        ]);
    }

    // ── Admin: aviso de conflito ──────────────────────────────────────────────

    public function test_admin_blocking_returns_conflicts_when_appointments_exist(): void
    {
        $employee = $this->makeEmployee();
        $existing = Appointment::factory()->create([
            'employee_id' => $employee->id,
            'starts_at'   => '2099-12-01 10:00:00',
            'ends_at'     => '2099-12-01 11:00:00',
            'status'      => 'scheduled',
        ]);

        $response = $this->actingAs($this->makeAdmin())
            ->postJson('/admin/blocked-slots', $this->blockPayload($employee));

        $response->assertOk();
        $response->assertJsonPath('conflicts.0.id', $existing->id);
    }

    // ── Admin: deletar bloqueio ───────────────────────────────────────────────

    public function test_admin_can_delete_blocked_slot(): void
    {
        $employee = $this->makeEmployee();
        $admin    = $this->makeAdmin();

        // Cria bloqueio via API e obtém o ID da resposta
        $response = $this->actingAs($admin)
             ->postJson('/admin/blocked-slots', $this->blockPayload($employee));

        $slotId = $response->json('slot.id');
        $this->assertNotNull($slotId, 'Bloqueio deveria ter sido criado com ID');

        // Confirma que o status no banco é 'blocked'
        $this->assertDatabaseHas('appointments', ['id' => $slotId, 'status' => 'blocked']);

        $this->actingAs($admin)
             ->delete("/admin/blocked-slots/{$slotId}")
             ->assertRedirect();

        $this->assertDatabaseMissing('appointments', ['id' => $slotId]);
    }

    // ── Admin: appointments-in-range ─────────────────────────────────────────

    public function test_appointments_in_range_returns_existing_appointments(): void
    {
        $employee = $this->makeEmployee();
        $existing = Appointment::factory()->create([
            'employee_id' => $employee->id,
            'starts_at'   => '2099-12-01 10:00:00',
            'ends_at'     => '2099-12-01 11:00:00',
            'status'      => 'scheduled',
        ]);

        $response = $this->actingAs($this->makeAdmin())
            ->getJson("/admin/blocked-slots/appointments-in-range?employee_id={$employee->id}&starts_at=2099-12-01+09:00:00&ends_at=2099-12-01+12:00:00");

        $response->assertOk()
                 ->assertJsonCount(1, 'appointments')
                 ->assertJsonPath('appointments.0.id', $existing->id);
    }

    // ── Colaborador: criar próprio bloqueio ───────────────────────────────────

    public function test_collaborator_can_create_blocked_slot_for_own_employee(): void
    {
        $employee = $this->makeEmployee();
        $user     = $this->makeCollaborator($employee);

        $this->actingAs($user)
             ->post('/collaborator/blocked-slots', [
                 'starts_at' => '2099-12-01 10:00',
                 'ends_at'   => '2099-12-01 12:00',
                 'notes'     => 'Pausa',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'employee_id' => $employee->id,
            'status'      => 'blocked',
        ]);
    }

    // ── Validações ────────────────────────────────────────────────────────────

    public function test_admin_block_fails_without_employee_id(): void
    {
        $this->actingAs($this->makeAdmin())
             ->post('/admin/blocked-slots', [
                 'starts_at' => '2099-12-01 10:00',
                 'ends_at'   => '2099-12-01 12:00',
             ])
             ->assertSessionHasErrors(['employee_id']);
    }

    public function test_admin_block_fails_when_end_before_start(): void
    {
        $employee = $this->makeEmployee();

        $this->actingAs($this->makeAdmin())
             ->post('/admin/blocked-slots', $this->blockPayload($employee, [
                 'starts_at' => '2099-12-01 12:00',
                 'ends_at'   => '2099-12-01 10:00',
             ]))
             ->assertSessionHasErrors(['ends_at']);
    }
}
