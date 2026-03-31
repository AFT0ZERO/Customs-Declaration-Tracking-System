<?php

namespace App\Services;

use App\Http\Requests\StoreCustomDeclarationRequest;
use App\Http\Requests\UpdateCustomDeclarationRequest;
use App\Models\CustomDeclaration;
use App\Repositories\CustomDeclarationRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CustomDeclarationService
{
    public function __construct(
        private readonly CustomDeclarationRepository $repository
    ) {}

    // ──────────────────────────────────────────────────────────────────────────
    //  index
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Build and return a paginated list of active declarations.
     *
     * Handles search-normalisation (>17 chars → substr) and
     * sort-column whitelist enforcement.
     */
    public function getPaginatedDeclarations(
        ?string $searchInput,
        string  $sort      = 'created_at',
        string  $direction = 'desc'
    ): LengthAwarePaginator {
        // Normalise the search term
        $search = $searchInput;
        if ($search !== null && strlen($search) > 17) {
            $search = substr($search, 17);
        }

        // Whitelist sort column to prevent SQL injection
        $allowedSortColumns = ['declaration_number', 'declaration_type', 'status', 'created_at', 'updated_at'];
        if (!in_array($sort, $allowedSortColumns)) {
            $sort = 'created_at';
        }

        return $this->repository->paginateActive($search, $sort, $direction);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  store
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Validate (already done by FormRequest), normalise the declaration number,
     * persist the new declaration, write the initial history entry, and
     * soft-delete immediately when the status is the archive status.
     */
    public function createDeclaration(StoreCustomDeclarationRequest $request, int $userId): CustomDeclaration
    {
        // Normalise the declaration number
        $declarationNumber = $request->declaration_number;
        if (strlen($declarationNumber) > 17) {
            $declarationNumber = substr($declarationNumber, 17);
        }

        // Persist the declaration
        $declaration = $this->repository->create([
            'declaration_number' => $declarationNumber,
            'declaration_type'   => $request->declaration_type,
            'year'               => $request->year,
            'status'             => $request->status,
        ]);

        // Record the initial history entry
        $this->repository->createHistory([
            'user_id'        => $userId,
            'declaration_id' => $declaration->id,
            'action'         => $request->status,
            'description'    => $request->description ?? 'لا يوجد',
        ]);

        // Archive immediately if the status demands it
        if ($declaration->status === 'العقبة الارشيف') {
            $this->repository->delete($declaration);
        }

        return $declaration;
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  updateStatus
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Apply changes to an existing declaration.
     *
     * Returns true when at least one field changed (so the controller can
     * redirect with the right flash message), false otherwise.
     */
    public function updateDeclaration(
        UpdateCustomDeclarationRequest $request,
        int $id,
        int $userId
    ): bool {
        $declaration = $this->repository->findOrFail($id);
        $hasChanges  = false;

        if ($declaration->declaration_number !== $request->editNumber) {
            $declaration->declaration_number = $request->editNumber;
            $hasChanges = true;
        }

        if ($declaration->declaration_type !== $request->declaration_type) {
            $declaration->declaration_type = $request->declaration_type;
            $hasChanges = true;
        }

        if ($declaration->year != $request->year) {
            $declaration->year = $request->year;
            $hasChanges = true;
        }

        if ($declaration->status !== $request->status) {
            $declaration->status = $request->status;
            $hasChanges = true;

            // Archive immediately when the new status demands it
            if ($request->status === 'العقبة الارشيف') {
                $this->repository->delete($declaration);
            }

            // Record the status-change history
            $this->repository->createHistory([
                'user_id'        => $userId,
                'declaration_id' => $declaration->id,
                'action'         => $request->status,
                'description'    => $request->editDescription ?? 'لا يوجد',
            ]);
        }

        if ($hasChanges) {
            $this->repository->save($declaration);
        }

        return $hasChanges;
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  showHistory
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Return the declaration (including soft-deleted) together with its history.
     *
     * @return array{declaration: CustomDeclaration, history: Collection}
     */
    public function getDeclarationWithHistory(int $id): array
    {
        $declaration = $this->repository->findWithTrashedOrFail($id);
        $history     = $this->repository->getHistory($declaration);

        Carbon::setLocale('ar');

        return compact('declaration', 'history');
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  showRestore
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Return the paginated list of soft-deleted declarations plus the
     * normalised search string (for re-populating the search input).
     *
     * @return array{declarations: LengthAwarePaginator, search: string|null}
     */
    public function getTrashedDeclarations(?string $searchInput, array $queryParams = []): array
    {
        $search = $searchInput;
        if ($search && strlen($search) > 17) {
            $search = substr($search, 17);
        }

        $declarations = $this->repository->paginateTrashed($search, $queryParams);

        return compact('declarations', 'search');
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  restore
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Restore a soft-deleted declaration.
     */
    public function restoreDeclaration(int $id): void
    {
        $declaration = $this->repository->findTrashed($id);
        $this->repository->restore($declaration);
    }
}
