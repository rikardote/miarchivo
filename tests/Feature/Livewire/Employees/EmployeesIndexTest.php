<?php

namespace Tests\Feature\Livewire\Employees;

use App\Models\Employee;
use App\Models\User;
use App\Livewire\Employees\Index;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EmployeesIndexTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->user = User::factory()->create();
    }

    public function test_it_can_render_employees_directory_with_pagination()
    {
        Employee::factory()->count(20)->create();

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertStatus(200)
            ->assertSeeHtml('table-premium');
    }

    public function test_it_can_search_employees_without_errors()
    {
        $employee = Employee::factory()->create([
            'first_name' => 'Alejandro',
            'last_name' => 'Gomez Martinez',
            'rfc' => 'GOMA850215',
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->set('search', 'GOMA850215')
            ->assertStatus(200)
            ->assertSee('GOMA850215')
            ->assertSee('ALEJANDRO');
    }
}
