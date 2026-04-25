<?php

namespace App\Livewire\Locations;

use App\Models\ArchiveLocation;
use App\Models\Branch;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Index extends Component
{
    use WithPagination, Toast;

    public string $search = '';
    public array $sortBy = ['column' => 'archive_name', 'direction' => 'asc'];

    // Modal & Form state
    public bool $locationModal = false;
    public ?ArchiveLocation $editing = null;
    
    public $branch_id;
    public $location_type;
    public $archive_name;
    public $cabinet;
    public $drawer;
    public $alpha_range;
    public $notes;
    public $is_active = true;

    protected $rules = [
        'branch_id' => 'required|exists:branches,id',
        'location_type' => 'required|string',
        'archive_name' => 'required|string',
        'cabinet' => 'nullable|string',
        'drawer' => 'nullable|string',
        'alpha_range' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function create()
    {
        $this->authorize('create', ArchiveLocation::class);
        $this->reset(['editing', 'branch_id', 'location_type', 'archive_name', 'cabinet', 'drawer', 'alpha_range', 'notes', 'is_active']);
        $this->is_active = true;
        $this->locationModal = true;
    }

    public function edit(ArchiveLocation $location)
    {
        $this->editing = $location;
        $this->branch_id = $location->branch_id;
        $this->location_type = $location->location_type;
        $this->archive_name = $location->archive_name;
        $this->cabinet = $location->cabinet;
        $this->drawer = $location->drawer;
        $this->alpha_range = $location->alpha_range;
        $this->notes = $location->notes;
        $this->is_active = $location->is_active;
        
        $this->locationModal = true;
    }

    public function save()
    {
        $data = $this->validate();
        $data['notes'] = $this->notes;

        if ($this->editing) {
            $this->editing->update($data);
            $this->success("Ubicación actualizada correctamente.");
        } else {
            ArchiveLocation::create($data);
            $this->success("Ubicación creada correctamente.");
        }

        $this->locationModal = false;
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search']);
        $this->resetPage();
    }

    public function render()
    {
        $this->authorize('viewAny', ArchiveLocation::class);

        $query = ArchiveLocation::with('branch')
            ->when($this->search, function ($query) {
                $query->where('archive_name', 'like', "%{$this->search}%")
                      ->orWhere('location_type', 'like', "%{$this->search}%")
                      ->orWhereHas('branch', function ($q) {
                          $q->where('name', 'like', "%{$this->search}%");
                      });
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        return view('livewire.locations.index', [
            'locations' => $query->paginate(10),
            'branches' => Branch::all(),
            'types' => [
                ['id' => 'Archivo Muerto', 'name' => 'Archivo Muerto'],
                ['id' => 'Archivo Activo', 'name' => 'Archivo Activo'],
                ['id' => 'Almacén Central', 'name' => 'Almacén Central'],
            ]
        ]);
    }
}
