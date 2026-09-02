<?php

namespace App\Livewire\Users;

use App\Enums\LoanStatus;
use App\Models\LoanRequest;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
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

    public string $selectedRole = '';

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function createUser()
    {
        $this->reset(['name', 'email', 'password', 'selectedRole', 'editingUser']);
        $this->userModal = true;
    }

    public function editUser(User $user)
    {
        $this->editingUser = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->selectedRole = $user->roles->first()?->name ?? '';
        $this->userModal = true;
    }

    public function saveUser()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.($this->editingUser->id ?? 'NULL'),
            'selectedRole' => 'required|string',
        ];

        if (! $this->editingUser || $this->password) {
            $rules['password'] = 'required|min:8';
        }

        $this->validate($rules);

        if ($this->editingUser) {
            $this->editingUser->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            if ($this->password) {
                $this->editingUser->update(['password' => bcrypt($this->password)]);
            }

            $this->editingUser->syncRoles([$this->selectedRole]);
            session()->flash('success', 'Usuario actualizado con éxito.');
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => bcrypt($this->password),
            ]);

            $user->assignRole($this->selectedRole);
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
            ->with(['roles', 'heldExpedients'])
            ->withCount('heldExpedients')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        return view('livewire.users.index', [
            'users' => $query->paginate(10),
            'roles' => Role::all(),
        ]);
    }
}
