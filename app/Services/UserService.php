<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private readonly UserRepository $repository
    ) {}

    // ──────────────────────────────────────────────────────────────────────────
    //  index
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Return a paginated list of users, optionally searched.
     */
    public function getPaginatedUsers(?string $search): LengthAwarePaginator
    {
        return $this->repository->paginate($search);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  store
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Validate is already handled by the FormRequest.
     * Hash the password and create the user.
     */
    public function createUser(array $validated): User
    {
        return $this->repository->create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'userId'   => $validated['userId'],
            'password' => Hash::make($validated['password']),
            'is_admin' => (bool) ($validated['is_admin'] ?? false),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  update
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Update user data.
     * Only re-hashes the password when a new one is supplied.
     */
    public function updateUser(User $user, array $validated): void
    {
        $password = !empty($validated['password'])
            ? Hash::make($validated['password'])
            : $user->password;                        // keep the existing hash

        $this->repository->update($user, [
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'userId'   => $validated['userId'],
            'password' => $password,
            'is_admin' => (bool) ($validated['is_admin'] ?? false),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  destroy
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Permanently delete a user.
     */
    public function deleteUser(User $user): void
    {
        $this->repository->delete($user);
    }
}
