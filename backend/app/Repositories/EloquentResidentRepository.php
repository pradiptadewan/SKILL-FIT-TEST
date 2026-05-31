<?php

namespace App\Repositories;

use App\Models\Resident;
use App\Repositories\Contracts\ResidentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentResidentRepository implements ResidentRepositoryInterface
{
    public function paginate(?string $search = null): LengthAwarePaginator
    {
        return Resident::query()
            ->with('currentOccupancy.house')
            ->when($search, fn ($query) => $query->where('full_name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15);
    }

    public function create(array $data): Resident
    {
        return Resident::create($data);
    }

    public function update(Resident $resident, array $data): Resident
    {
        $resident->update($data);

        return $resident->refresh();
    }

    public function delete(Resident $resident): void
    {
        $resident->delete();
    }
}
