<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository
{
    /**
     * Return a paginated list of users, optionally filtered by name or userId.
     */
    public function paginate(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return User::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('userId', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Persist a new user from the given attribute array.
     */
    public function create(array $data): User
    {
        $user           = new User();
        $user->name     = $data['name'];
        $user->email    = $data['email'];
        $user->userId   = $data['userId'];
        $user->password = $data['password'];          // already hashed by Service
        $user->is_admin = $data['is_admin'] ?? false;
        $user->save();

        return $user;
    }

    /**
     * Update and persist an existing user.
     */
    public function update(User $user, array $data): void
    {
        $user->name     = $data['name'];
        $user->email    = $data['email'];
        $user->userId   = $data['userId'];
        $user->password = $data['password'];          // already hashed by Service
        $user->is_admin = $data['is_admin'] ?? false;
        $user->save();
    }

    /**
     * Hard-delete a user.
     */
    public function delete(User $user): void
    {
        $user->delete();
    }
}
