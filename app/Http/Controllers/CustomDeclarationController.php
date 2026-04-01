<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomDeclarationRequest;
use App\Http\Requests\UpdateCustomDeclarationRequest;
use App\Services\CustomDeclarationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomDeclarationController extends Controller
{
    public function __construct(
        private readonly CustomDeclarationService $service
    ) {}

    // ──────────────────────────────────────────────────────────────────────────
    //  GET /dashboard
    // ──────────────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $declarations = $this->service->getPaginatedDeclarations(
            searchInput: $request->query('search'),
            sort:        $request->query('sort', 'created_at'),
            direction:   $request->query('direction', 'desc'),
        );

        return view('dashboard', compact('declarations'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  POST /declaration/store
    // ──────────────────────────────────────────────────────────────────────────

    public function store(StoreCustomDeclarationRequest $request): RedirectResponse
    {
        $this->service->createDeclaration($request, auth()->id());

        return redirect()->back()->with('success', 'تم إضافة البيان الجمركي بنجاح!');
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  PUT /declaration/update/{id}
    // ──────────────────────────────────────────────────────────────────────────

    public function updateStatus(UpdateCustomDeclarationRequest $request, int $id): RedirectResponse
    {
        $changed = $this->service->updateDeclaration($request, $id, auth()->id());

        return $changed
            ? redirect()->back()->with('success', 'تم تحديث الحالة بنجاح!')
            : redirect()->back()->with('info', 'لم يتم تغيير الحالة');
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  GET /declaration/history/{id}
    // ──────────────────────────────────────────────────────────────────────────

    public function showHistory(int $id): View
    {
        ['declaration' => $declaration, 'history' => $history] =
            $this->service->getDeclarationWithHistory($id);

        return view('history', compact('history', 'declaration'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  GET /declaration/restore
    // ──────────────────────────────────────────────────────────────────────────

    public function showRestore(Request $request): View
    {
        ['declarations' => $declarations, 'search' => $search] =
            $this->service->getTrashedDeclarations(
                searchInput: $request->input('search'),
                sort:        $request->query('sort', 'created_at'),
                direction:   $request->query('direction', 'desc'),
            );

        return view('restore', compact('declarations', 'search'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  GET /dashboard/restore/{id}
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Restore a soft-deleted declaration.
     */
    public function restore(int $id): RedirectResponse
    {
        $this->service->restoreDeclaration($id);

        session()->flash('success', 'تم استرجاع البيان');

        return to_route('declaration.showRestore');
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  GET /analytics
    // ──────────────────────────────────────────────────────────────────────────

    public function showAnalytics(): View
    {
        $data = $this->service->getAnalyticsData();

        return view('analytics', $data);
    }
}
