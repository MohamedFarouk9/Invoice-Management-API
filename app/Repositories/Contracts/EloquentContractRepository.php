<?php

namespace App\Repositories\Contracts;

use App\Enums\ContractStatus;
use App\Models\Contract;
use Illuminate\Database\Eloquent\Collection;

class EloquentContractRepository implements ContractRepositoryInterface
{
    public function findById(int $id): ?Contract
    {
        return Contract::find($id);
    }

    public function findByIdOrFail(int $id): Contract
    {
        return Contract::findOrFail($id);
    }

    public function create(array $data): Contract
    {
        return Contract::create($data);
    }

    public function update(int $id, array $data): Contract
    {
        $contract = $this->findByIdOrFail($id);
        $contract->update($data);
        return $contract->fresh();
    }

    public function getByTenant(int $tenantId): Collection
    {
        return Contract::where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getActiveByTenant(int $tenantId): Collection
    {
        return Contract::where('tenant_id', $tenantId)
            ->where('status', ContractStatus::ACTIVE->value)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByStatus(int $tenantId, string $status): Collection
    {
        return Contract::where('tenant_id', $tenantId)
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function delete(int $id): bool
    {
        return (bool) Contract::destroy($id);
    }
}
