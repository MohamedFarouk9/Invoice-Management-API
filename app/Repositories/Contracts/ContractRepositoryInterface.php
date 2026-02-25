<?php

namespace App\Repositories\Contracts;

use App\Models\Contract;
use Illuminate\Database\Eloquent\Collection;


interface ContractRepositoryInterface
{

    public function findById(int $id): ?Contract;

   
    public function findByIdOrFail(int $id): Contract;

   
    public function create(array $data): Contract;

   
    public function update(int $id, array $data): Contract;

   
    public function getByTenant(int $tenantId): Collection;

   
    public function getActiveByTenant(int $tenantId): Collection;

   
    public function getByStatus(int $tenantId, string $status): Collection;

   
    public function delete(int $id): bool;
}
