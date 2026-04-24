<?php

namespace App\Livewire\Expedients;

use App\Models\Expedient;
use App\Enums\ExpedientStatus;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Illuminate\Database\Eloquent\Builder;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'status']);
        $this->resetPage();
    }

    public function render()
    {
        $expedients = Expedient::query()
            ->with(['employee', 'currentLocation'])
            ->when($this->search, fn (Builder $q) => $q->search($this->search))
            ->when($this->status, fn (Builder $q) => $q->where('current_status', $this->status))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(10);

        return view('livewire.expedients.index', [
            'expedients' => $expedients,
            'statuses' => ExpedientStatus::cases(),
        ]);
    }
}
