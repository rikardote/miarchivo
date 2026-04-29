<?php

namespace App\Livewire\Expedients;

use App\Models\Expedient;
use App\Enums\ExpedientStatus;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Illuminate\Database\Eloquent\Builder;

use Mary\Traits\Toast;

class Index extends Component
{
    use WithPagination, Toast;

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
            'targetLocationId' => 'required|exists:archive_locations,id'
        ]);

        $count = count($this->selected);
        Expedient::whereIn('id', $this->selected)->update([
            'current_location_id' => $this->targetLocationId
        ]);

        // Registrar movimiento en el historial para cada uno
        foreach ($this->selected as $id) {
            \App\Models\ExpedientMovement::create([
                'expedient_id' => $id,
                'user_id' => auth()->id(),
                'movement_type' => \App\Enums\MovementType::Relocated,
                'notes' => 'Movimiento masivo de ubicación.'
            ]);
        }

        $this->success("$count expedientes movidos con éxito.");
        $this->reset(['selected', 'bulkMoveModal', 'targetLocationId']);
    }

    public function render()
    {
        $expedients = Expedient::query()
            ->with(['employee.branch', 'currentLocation'])
            ->when($this->search, fn (Builder $q) => $q->search($this->search))
            ->when($this->status, fn (Builder $q) => $q->where('current_status', $this->status))
            ->when($this->filter === 'pending_transfer', function($q) {
                $q->whereHas('employee', fn($e) => $e->where('employment_status', 'inactive'))
                  ->whereHas('currentLocation.branch', fn($b) => $b->where('code', 'MEX'));
            })
            ->when($this->branch_id, fn (Builder $q) => $q->whereHas('employee', fn($e) => $e->where('branch_id', $this->branch_id)))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(10);

        return view('livewire.expedients.index', [
            'expedients' => $expedients,
            'statuses' => collect(ExpedientStatus::cases())->map(fn($status) => [
                'name' => $status->label(),
                'value' => $status->value,
            ]),
            'branches' => \App\Models\Branch::all(),
            'locations' => \App\Models\ArchiveLocation::with('branch')->get(),
        ]);
    }
}
