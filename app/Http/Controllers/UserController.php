<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $service
    ) {}

    // ──────────────────────────────────────────────────────────────────────────
    //  GET /users
    // ──────────────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $search = $request->get('search');
        $users  = $this->service->getPaginatedUsers($search);

        return view('users.index', compact('users', 'search'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  GET /users/create
    // ──────────────────────────────────────────────────────────────────────────

    public function create(): View
    {
        return view('users.create');
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  POST /users
    // ──────────────────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'userId'   => ['required', 'string', 'max:255', 'unique:users,userId'],
            'password' => ['required', 'string', 'min:8'],
            'is_admin' => ['sometimes', 'boolean'],
        ]);

        $this->service->createUser($validated);

        return redirect()->route('users.index')->with('success', 'تم إنشاء المستخدم بنجاح');
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  GET /users/{user}/edit
    // ──────────────────────────────────────────────────────────────────────────

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  PUT /users/{user}
    // ──────────────────────────────────────────────────────────────────────────

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'userId'   => ['required', 'string', 'max:255', Rule::unique('users', 'userId')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'is_admin' => ['sometimes', 'boolean'],
        ]);

        $this->service->updateUser($user, $validated);

        return redirect()->route('users.index')->with('success', 'تم تحديث بيانات المستخدم');
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  DELETE /users/{user}
    // ──────────────────────────────────────────────────────────────────────────

    public function destroy(User $user): RedirectResponse
    {
        $this->service->deleteUser($user);

        return redirect()->route('users.index')->with('success', 'تم حذف المستخدم');
    }
}