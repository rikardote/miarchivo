<?php

namespace App\Livewire\Users;

use App\Enums\LoanStatus;
use App\Models\LoanRequest;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $userModal = false;

    public ?User $editingUser = null;

    // Form fields
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public array $selectedRoles = [];

    // New role definition fields
    public bool $newRoleModal = false;

    public string $newRoleName = '';

    public array $newRolePermissions = [];

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function createUser()
    {
        $this->reset(['name', 'email', 'password', 'editingUser']);
        $this->selectedRoles = ['user'];
        $this->userModal = true;
    }

    public function editUser(User $user)
    {
        $this->editingUser = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        if (empty($this->selectedRoles)) {
            $this->selectedRoles = ['user'];
        }
        $this->userModal = true;
    }

    public function toggleRole(string $roleName): void
    {
        if (in_array($roleName, $this->selectedRoles)) {
            if (count($this->selectedRoles) > 1) {
                $this->selectedRoles = array_values(array_diff($this->selectedRoles, [$roleName]));
            }
        } else {
            $this->selectedRoles[] = $roleName;
        }
    }

    public function openNewRoleModal(): void
    {
        $this->reset(['newRoleName', 'newRolePermissions']);
        $this->newRoleModal = true;
    }

    public function saveNewRole(): void
    {
        $this->validate([
            'newRoleName' => 'required|string|min:3|max:50|regex:/^[a-zA-Z0-9_\-]+$/|unique:roles,name',
        ], [
            'newRoleName.required' => 'El identificador del rol es obligatorio.',
            'newRoleName.regex' => 'El nombre del rol solo puede contener letras, números, guiones y guiones bajos.',
            'newRoleName.unique' => 'Ya existe un rol con este nombre.',
        ]);

        $roleName = strtolower(trim($this->newRoleName));
        $role = Role::create(['name' => $roleName]);

        if (! empty($this->newRolePermissions)) {
            $role->syncPermissions($this->newRolePermissions);
        }

        if (! in_array($role->name, $this->selectedRoles)) {
            $this->selectedRoles[] = $role->name;
        }

        $this->newRoleModal = false;
        session()->flash('success', "Rol '{$role->name}' creado y asignado con éxito.");
    }

    public function getRoleMeta(string $roleName, int $permissionsCount = 0): array
    {
        $map = [
            'superuser' => [
                'title' => 'Super Administrador',
                'badge' => 'badge-primary',
                'badge_color' => 'bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800',
                'icon' => 'o-shield-check',
                'description' => 'Control total del sistema, configuración global, auditorías y eliminación de registros.',
            ],
            'admin' => [
                'title' => 'Encargado / Administrador',
                'badge' => 'badge-info',
                'badge_color' => 'bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                'icon' => 'o-building-library',
                'description' => 'Gestión integral de expedientes, aprobación de préstamos, gestión de ubicaciones y reportes.',
            ],
            'operator' => [
                'title' => 'Operador de Archivo',
                'badge' => 'badge-warning',
                'badge_color' => 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                'icon' => 'o-qr-code',
                'description' => 'Escaneo físico (móvil y PC), devolución directa a gavetas y movimientos en mostrador.',
            ],
            'auditor' => [
                'title' => 'Auditor de Inventario',
                'badge' => 'badge-success',
                'badge_color' => 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                'icon' => 'o-clipboard-document-check',
                'description' => 'Conciliación física en tiempo real por archivero/caja y actas oficiales de auditoría.',
            ],
            'user' => [
                'title' => 'Usuario de Consulta',
                'badge' => 'badge-ghost',
                'badge_color' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700',
                'icon' => 'o-user',
                'description' => 'Consulta de catálogo de expedientes, solicitud de préstamos y seguimiento.',
            ],
        ];

        return $map[$roleName] ?? [
            'title' => ucfirst($roleName),
            'badge' => 'badge-ghost',
            'badge_color' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700',
            'icon' => 'o-key',
            'description' => "Rol con {$permissionsCount} permiso(s) asignado(s).",
        ];
    }

    public function saveUser()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.($this->editingUser->id ?? 'NULL'),
            'selectedRoles' => 'required|array|min:1',
            'selectedRoles.*' => 'exists:roles,name',
        ];

        if (! $this->editingUser || $this->password) {
            $rules['password'] = 'required|min:8';
        }

        $this->validate($rules, [
            'selectedRoles.required' => 'Debes asignar al menos un rol al usuario.',
            'selectedRoles.min' => 'Debes asignar al menos un rol al usuario.',
        ]);

        if ($this->editingUser) {
            $this->editingUser->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            if ($this->password) {
                $this->editingUser->update(['password' => bcrypt($this->password)]);
            }

            $this->editingUser->syncRoles($this->selectedRoles);
            session()->flash('success', 'Usuario actualizado con éxito.');
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => bcrypt($this->password),
            ]);

            $user->syncRoles($this->selectedRoles);
            session()->flash('success', 'Usuario creado con éxito.');
        }

        $this->userModal = false;
    }

    public bool $custodyModal = false;

    public ?User $custodyUser = null;

    public $custodyLoans = [];

    public function showCustody(User $user)
    {
        $this->custodyUser = $user->load(['heldExpedients.employee']);
        $this->custodyLoans = LoanRequest::where(function ($query) use ($user) {
            $query->where('requester_id', $user->id)
                ->orWhereIn('expedient_id', $user->heldExpedients->pluck('id'));
        })
            ->where('status', LoanStatus::Delivered)
            ->with(['expedient.employee'])
            ->get();

        $this->custodyModal = true;
    }

    public function clearFilters()
    {
        $this->reset(['search']);
        $this->resetPage();
    }

    public function render()
    {
        $this->authorize('viewAny', User::class);

        $query = User::query()
            ->with(['roles.permissions', 'heldExpedients'])
            ->withCount('heldExpedients')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        $availablePermissions = Permission::orderBy('name')
            ->get()
            ->groupBy(function ($permission) {
                $parts = explode('.', $permission->name);

                return match ($parts[0]) {
                    'expedients' => 'Expedientes',
                    'loans' => 'Préstamos',
                    'locations' => 'Ubicaciones',
                    'users' => 'Usuarios',
                    'employees' => 'Empleados',
                    'movements' => 'Movimientos',
                    'dashboard' => 'Tablero',
                    'settings' => 'Configuración',
                    default => ucfirst($parts[0]),
                };
            });

        return view('livewire.users.index', [
            'users' => $query->paginate(10),
            'roles' => Role::with('permissions')->get(),
            'availablePermissions' => $availablePermissions,
        ]);
    }
}
