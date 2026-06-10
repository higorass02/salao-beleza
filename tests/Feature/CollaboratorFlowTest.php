<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Service;
use App\Models\User;
use App\Notifications\WelcomeEmployeeNotification;
use App\Services\EmployeeService;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Cobre o fluxo de colaborador:
 * - Criação de usuário ao criar funcionário
 * - Redirect de login por papel (admin vs colaborador)
 * - Proteção de rotas pelos middlewares EnsureIsAdmin / EnsureIsCollaborator
 * - Agendamento via rota do colaborador (employee_id forçado do auth)
 */
class CollaboratorFlowTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(str_repeat('a', 32)));
        $app['config']->set('auth.providers.users.model', \App\Models\User::class);
        $app['router']->aliasMiddleware('admin',            \App\Http\Middleware\EnsureIsAdmin::class);
        $app['router']->aliasMiddleware('collaborator',     \App\Http\Middleware\EnsureIsCollaborator::class);
        $app['router']->aliasMiddleware('password.changed', \App\Http\Middleware\EnsurePasswordChanged::class);
    }

    protected function defineRoutes($router): void
    {
        $router->middleware('web')->group(function () {
            require __DIR__ . '/../../routes/web.php';
        });
    }

    // ── EmployeeService: criação de usuário ───────────────────────────────────

    public function test_create_employee_also_creates_linked_user(): void
    {
        Notification::fake();

        $service  = $this->app->make(EmployeeService::class);
        $employee = $service->create([
            'name'   => 'Fernanda',
            'email'  => 'fernanda@salao.com',
            'phone'  => '11999990001',
            'role'   => 'Cabeleireira',
            'active' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'email'       => 'fernanda@salao.com',
            'employee_id' => $employee->id,
            'is_admin'    => false,
        ]);
    }

    public function test_create_employee_sends_welcome_notification(): void
    {
        Notification::fake();

        $service = $this->app->make(EmployeeService::class);
        $service->create([
            'name'   => 'Roberto',
            'email'  => 'roberto@salao.com',
            'phone'  => '11999990002',
            'role'   => 'Barbeiro',
            'active' => true,
        ]);

        $user = User::where('email', 'roberto@salao.com')->first();
        Notification::assertSentTo($user, WelcomeEmployeeNotification::class);
    }

    // ── Login redirect por papel ──────────────────────────────────────────────

    public function test_admin_login_redirects_to_dashboard(): void
    {
        $admin = User::factory()->create([
            'password'    => bcrypt('senha123'),
            'is_admin'    => true,
            'employee_id' => null,
        ]);

        $this->post('/login', ['email' => $admin->email, 'password' => 'senha123'])
             ->assertRedirect(route('dashboard'));
    }

    public function test_collaborator_login_redirects_to_collaborator_dashboard(): void
    {
        $employee = Employee::factory()->create();
        $user     = User::factory()->create([
            'password'    => bcrypt('senha123'),
            'is_admin'    => false,
            'employee_id' => $employee->id,
        ]);

        $this->post('/login', ['email' => $user->email, 'password' => 'senha123'])
             ->assertRedirect(route('collaborator.dashboard'));
    }

    // ── Proteção de rotas ─────────────────────────────────────────────────────

    public function test_admin_route_is_blocked_for_non_admin(): void
    {
        $employee = Employee::factory()->create();
        $user     = User::factory()->create(['is_admin' => false, 'employee_id' => $employee->id]);

        $this->actingAs($user)
             ->get('/dashboard')
             ->assertForbidden();
    }

    public function test_collaborator_route_is_blocked_for_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'employee_id' => null]);

        $this->actingAs($admin)
             ->get('/collaborator')
             ->assertForbidden();
    }

    public function test_collaborator_route_is_blocked_for_unauthenticated(): void
    {
        $this->get('/collaborator')
             ->assertRedirect('/login');
    }

    // ── Agendamento via rota de colaborador ───────────────────────────────────

    public function test_collaborator_can_book_appointment_for_own_employee(): void
    {
        $employee = Employee::factory()->create(['active' => true]);
        $user     = User::factory()->create(['is_admin' => false, 'employee_id' => $employee->id]);
        $client   = Client::factory()->create();
        $service  = Service::factory()->create(['active' => true, 'duration_minutes' => 60]);

        $this->actingAs($user)
             ->post('/collaborator/appointments', [
                 'client_id'  => $client->id,
                 'service_id' => $service->id,
                 'starts_at'  => '2026-07-01 10:00:00',
             ])
             ->assertRedirect(route('collaborator.appointments.index'));

        $this->assertDatabaseHas('appointments', [
            'client_id'   => $client->id,
            'employee_id' => $employee->id,
            'service_id'  => $service->id,
            'starts_at'   => '2026-07-01 10:00:00',
            'status'      => 'scheduled',
        ]);
    }

    public function test_collaborator_appointment_employee_id_is_forced_from_auth(): void
    {
        $employee      = Employee::factory()->create(['active' => true]);
        $otherEmployee = Employee::factory()->create(['active' => true]);
        $user          = User::factory()->create(['is_admin' => false, 'employee_id' => $employee->id]);
        $client        = Client::factory()->create();
        $service       = Service::factory()->create(['active' => true, 'duration_minutes' => 60]);

        $this->actingAs($user)
             ->post('/collaborator/appointments', [
                 'client_id'  => $client->id,
                 'service_id' => $service->id,
                 'starts_at'  => '2026-07-01 11:00:00',
             ]);

        // Independente do que o cliente envie, employee_id vem do auth
        $this->assertDatabaseHas('appointments', [
            'employee_id' => $employee->id,
        ]);
        $this->assertDatabaseMissing('appointments', [
            'employee_id' => $otherEmployee->id,
        ]);
    }

    public function test_collaborator_cannot_edit_another_employees_appointment(): void
    {
        $employee      = Employee::factory()->create(['active' => true]);
        $otherEmployee = Employee::factory()->create(['active' => true]);
        $user          = User::factory()->create(['is_admin' => false, 'employee_id' => $employee->id]);

        $appointment = Appointment::factory()->create([
            'employee_id' => $otherEmployee->id,
            'starts_at'   => '2026-07-02 10:00:00',
            'ends_at'     => '2026-07-02 11:00:00',
        ]);

        $this->actingAs($user)
             ->get("/collaborator/appointments/{$appointment->id}/edit")
             ->assertForbidden();
    }
}
