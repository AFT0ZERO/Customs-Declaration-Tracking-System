<?php

namespace App\Repositories;

use App\Models\CustomDeclaration;
use App\Models\DeclarationHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CustomDeclarationRepository
{
    // ──────────────────────────────────────────────────────────────────────────
    //  Active declarations
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Return a paginated list of active (non-deleted) declarations.
     *
     * @param string|null $search    Filtered declaration_number (already normalised by caller).
     * @param string      $sort      Column to sort by (already validated by caller).
     * @param string      $direction 'asc' | 'desc'
     * @param int         $perPage
     */
    public function paginateActive(
        ?string $search,
        string $sort,
        string $direction,
        int $perPage = 50
    ): LengthAwarePaginator {
        $query = CustomDeclaration::query();

        if (!empty($search)) {
            $query->where('declaration_number', '=', $search);
        }
        if ($sort === 'declaration_number') {
            $query->orderByRaw("CAST(declaration_number AS UNSIGNED) " . $direction);
        } else {
            $query->orderBy($sort, $direction);
        }
        
        return $query->paginate($perPage);
    }

    /**
     * Create a new declaration record.
     */
    public function create(array $data): CustomDeclaration
    {
        return CustomDeclaration::create($data);
    }

    /**
     * Find an active declaration or throw ModelNotFoundException.
     */
    public function findOrFail(int $id): CustomDeclaration
    {
        return CustomDeclaration::findOrFail($id);
    }

    /**
     * Persist changes to an existing declaration.
     */
    public function save(CustomDeclaration $declaration): void
    {
        $declaration->save();
    }

    /**
     * Soft-delete a declaration.
     */
    public function delete(CustomDeclaration $declaration): void
    {
        $declaration->delete();
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Trashed (soft-deleted) declarations
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Find a declaration including soft-deleted ones, or throw.
     */
    public function findWithTrashedOrFail(int $id): CustomDeclaration
    {
        return CustomDeclaration::withTrashed()->findOrFail($id);
    }

    /**
     * Find a soft-deleted declaration (returns null if not found).
     */
    public function findTrashed(int $id): ?CustomDeclaration
    {
        return CustomDeclaration::withTrashed()->find($id);
    }

    /**
     * Restore a soft-deleted declaration.
     */
    public function restore(CustomDeclaration $declaration): void
    {
        $declaration->restore();
    }

    /**
     * Return paginated trashed declarations, optionally filtered by declaration_number.
     *
     * @param string|null $search   Already-normalised search string.
     * @param string      $sort     Column to sort by (already validated by caller).
     * @param string      $direction 'asc' | 'desc'
     * @param int         $perPage
     */
    public function paginateTrashed(
        ?string $search,
        string $sort,
        string $direction,
        int $perPage = 50
    ): LengthAwarePaginator {
        $query = CustomDeclaration::onlyTrashed();

        if (!empty($search)) {
            $query->where('declaration_number', '=', $search);
        }

        if ($sort === 'declaration_number') {
            $query->orderByRaw("CAST(declaration_number AS UNSIGNED) " . $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        return $query->paginate($perPage);
    }

    /**
     * Return ordered history records for a given declaration.
     */
    public function getHistory(CustomDeclaration $declaration): Collection
    {
        return $declaration->histories()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Declaration History
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Create a history entry for a declaration.
     */
    public function createHistory(array $data): DeclarationHistory
    {
        return DeclarationHistory::create($data);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Analytics
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Get declaration counts grouped by year.
     */
    public function getCountsByYear(): Collection
    {
        return CustomDeclaration::selectRaw('year, COUNT(*) as count')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();
    }

    /**
     * Get declaration counts grouped by status.
     */
    public function getCountsByStatus(): Collection
    {
        return CustomDeclaration::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();
    }
}
