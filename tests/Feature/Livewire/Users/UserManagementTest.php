<?php

namespace Tests\Feature\Livewire\Users;

use App\Livewire\Users\Index;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superuser;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->superuser = User::factory()->create(['name' => 'Admin Boss']);
        $this->superuser->assignRole('superuser');

        $this->regularUser = User::factory()->create(['name' => 'Normal Employee']);
        $this->regularUser->assignRole('user');
    }

    public function test_unauthorized_user_cannot_access_user_management(): void
    {
        Livewire::actingAs($this->regularUser)
            ->test(Index::class)
            ->assertForbidden();
    }

    public function test_can_create_user_with_multiple_roles(): void
    {
        Livewire::actingAs($this->superuser)
            ->test(Index::class)
            ->call('createUser')
            ->assertSet('userModal', true)
            ->set('name', 'Carlos Mendoza')
            ->set('email', 'carlos.mendoza@archivo.gob.mx')
            ->set('password', 'password123')
            ->call('toggleRole', 'auditor')
            ->call('saveUser')
            ->assertHasNoErrors()
            ->assertSet('userModal', false);

        $created = User::where('email', 'carlos.mendoza@archivo.gob.mx')->first();
        $this->assertNotNull($created);
        $this->assertEquals('Carlos Mendoza', $created->name);
        $this->assertTrue($created->hasRole('user'));
        $this->assertTrue($created->hasRole('auditor'));
    }

    public function test_can_edit_existing_user_roles(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'Laura Gomez',
            'email' => 'laura@archivo.gob.mx',
        ]);
        $targetUser->assignRole('user');

        Livewire::actingAs($this->superuser)
            ->test(Index::class)
            ->call('editUser', $targetUser->id)
            ->assertSet('editingUser.id', $targetUser->id)
            ->assertSet('name', 'Laura Gomez')
            ->call('toggleRole', 'operator')
            ->call('saveUser')
            ->assertHasNoErrors();

        $targetUser->refresh();
        $this->assertTrue($targetUser->hasRole('operator'));
        $this->assertTrue($targetUser->hasRole('user'));
    }

    public function test_cannot_save_user_without_any_role(): void
    {
        Livewire::actingAs($this->superuser)
            ->test(Index::class)
            ->call('createUser')
            ->set('name', 'Test Sin Rol')
            ->set('email', 'sinrol@archivo.gob.mx')
            ->set('password', 'password123')
            ->set('selectedRoles', [])
            ->call('saveUser')
            ->assertHasErrors(['selectedRoles']);
    }

    public function test_can_define_new_role_with_granular_permissions(): void
    {
        Livewire::actingAs($this->superuser)
            ->test(Index::class)
            ->call('openNewRoleModal')
            ->assertSet('newRoleModal', true)
            ->set('newRoleName', 'coordinador_archivo')
            ->set('newRolePermissions', ['expedients.view', 'expedients.update', 'loans.view'])
            ->call('saveNewRole')
            ->assertHasNoErrors()
            ->assertSet('newRoleModal', false);

        $role = Role::findByName('coordinador_archivo');
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('expedients.view'));
        $this->assertTrue($role->hasPermissionTo('expedients.update'));
        $this->assertTrue($role->hasPermissionTo('loans.view'));
        $this->assertFalse($role->hasPermissionTo('users.delete'));
    }

    public function test_validates_new_role_name(): void
    {
        Livewire::actingAs($this->superuser)
            ->test(Index::class)
            ->call('openNewRoleModal')
            ->set('newRoleName', 'admin') // Already exists
            ->call('saveNewRole')
            ->assertHasErrors(['newRoleName']);

        Livewire::actingAs($this->superuser)
            ->test(Index::class)
            ->call('openNewRoleModal')
            ->set('newRoleName', 'rol con espacios') // Invalid format
            ->call('saveNewRole')
            ->assertHasErrors(['newRoleName']);
    }
}
