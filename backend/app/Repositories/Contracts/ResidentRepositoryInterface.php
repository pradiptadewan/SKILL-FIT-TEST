<?php

namespace App\Repositories\Contracts;

use App\Models\Resident;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ResidentRepositoryInterface
{
    public function paginate(?string $search = null): LengthAwarePaginator;

    public function create(array $data): Resident;

    public function update(Resident $resident, array $data): Resident;

    public function delete(Resident $resident): void;
}
