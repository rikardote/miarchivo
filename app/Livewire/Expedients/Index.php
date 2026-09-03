<?php

namespace App\Livewire\Expedients;

use App\Enums\ExpedientStatus;
use App\Enums\MovementType;
use App\Models\ArchiveLocation;
use App\Models\Branch;
use App\Models\Expedient;
use App\Models\ExpedientMovement;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Index extends Component
{
    use Toast, WithPagination;

    #[Url]
    public string $search = '';

    public array $selected = [];

    public bool $bulkMoveModal = false;

    public ?int $targetLocationId = null;

    public bool $showGlossary = false;

    #[Url]
    public string $status = '';

    #[Url]
    public ?int $branch_id = null;

    #[Url]
    public string $filter = '';

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingBranchId()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'status', 'branch_id', 'selected']);
        $this->resetPage();
    }

    public function showBulkMove()
    {
        if (empty($this->selected)) {
            $this->error('Seleccione al menos un expediente.');

            return;
        }
        $this->bulkMoveModal = true;
    }

    public function executeBulkMove()
    {
        $this->validate([
            'targetLocationId' => 'required|exists:archive_locations,id',
        ]);

        $count = count($this->selected);
        Expedient::whereIn('id', $this->selected)->update([
            'current_location_id' => $this->targetLocationId,
        ]);

        // Registrar movimiento en el historial para cada uno
        foreach ($this->selected as $id) {
            ExpedientMovement::create([
                'expedient_id' => $id,
                'user_id' => auth()->id(),
                'movement_type' => MovementType::Relocated,
                'notes' => 'Movimiento masivo de ubicación.',
            ]);
        }

        $this->success("$count expedientes movidos con éxito.");
        $this->reset(['selected', 'bulkMoveModal', 'targetLocationId']);
    }

    protected function applySorting(Builder $query): void
    {
        $column = $this->sortBy['column'] ?? 'created_at';
        $direction = strtolower($this->sortBy['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        if ($column === 'expedient' || $column === 'expedient_code') {
            $query->orderBy('expedients.expedient_code', $direction);
        } elseif ($column === 'employee.last_name' || $column === 'employee' || $column === 'employee.name') {
            $query->join('employees as sort_emp', 'expedients.employee_id', '=', 'sort_emp.id')
                ->orderBy('sort_emp.last_name', $direction)
                ->orderBy('sort_emp.first_name', $direction)
                ->select('expedients.*');
        } elseif ($column === 'employee.branch.name') {
            $query->join('employees as branch_emp', 'expedients.employee_id', '=', 'branch_emp.id')
                ->leftJoin('branches as emp_branches', 'branch_emp.branch_id', '=', 'emp_branches.id')
                ->orderBy('emp_branches.name', $direction)
                ->select('expedients.*');
        } elseif ($column === 'currentLocation.short_label') {
            $query->leftJoin('archive_locations', 'expedients.current_location_id', '=', 'archive_locations.id')
                ->orderBy('archive_locations.cabinet', $direction)
                ->orderBy('archive_locations.drawer', $direction)
                ->select('expedients.*');
        } elseif (in_array($column, ['volume_number', 'current_status', 'created_at', 'id'])) {
            $query->orderBy("expedients.{$column}", $direction);
        } else {
            $query->orderBy('expedients.created_at', 'desc');
        }
    }

    public function render()
    {
        $isAdmin = auth()->user()->can('expedients.create');
        $searchTerm = trim($this->search);

        if (! $isAdmin && mb_strlen($searchTerm) < 2) {
            $expedients = Expedient::query()->whereRaw('1 = 0')->paginate(10);
        } else {
            $query = Expedient::query()
                ->with(['employee.branch', 'currentLocation'])
                ->when($this->search, fn (Builder $q) => $q->search($this->search))
                ->when($this->status, fn (Builder $q) => $q->where('current_status', $this->status))
                ->when($this->filter === 'pending_transfer', function ($q) {
                    $q->whereHas('employee', fn ($e) => $e->where('employment_status', 'inactive'))
                        ->whereHas('currentLocation.branch', fn ($b) => $b->where('code', 'MEX'));
                })
                ->when($this->branch_id, fn (Builder $q) => $q->whereHas('employee', fn ($e) => $e->where('branch_id', $this->branch_id)));

            $this->applySorting($query);
            $expedients = $query->paginate(10);
        }

        return view('livewire.expedients.index', [
            'expedients' => $expedients,
            'isAdmin' => $isAdmin,
            'statuses' => collect(ExpedientStatus::cases())->map(fn ($status) => [
                'name' => $status->label(),
                'value' => $status->value,
            ]),
            'branches' => Branch::all(),
            'locations' => ArchiveLocation::with('branch')->get(),
        ]);
    }
}
