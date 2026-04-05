<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Feature tests for UserController.
 *
 * These tests cover the current behaviour so that we can safely refactor
 * towards a Service / Repository architecture without breaking anything.
 */
class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function adminUser(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function regularUser(): User
    {
        return User::factory()->create(['is_admin' => false]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  index() – GET /users
    // ──────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_users_index_requires_authentication(): void
    {
        $response = $this->get(route('users.index'));

        // The login route in this project resolves to '/' (the homepage)
        $response->assertRedirect('/');
    }

    /** @test */
    public function test_non_admin_user_cannot_access_users_index(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->get(route('users.index'));

        // The 'admin' middleware should redirect or return 403
        $response->assertStatus(403);
    }

    /** @test */
    public function test_admin_can_view_users_index(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertOk();
        $response->assertViewIs('users.index');
    }

    /** @test */
    public function test_users_index_paginates_results(): void
    {
        $admin = $this->adminUser();
        // Use explicit unique userIds so the factory doesn't collide with rand(1,10)
        foreach (range(1, 15) as $i) {
            User::factory()->create(['userId' => 'PAGINATEUID' . $i]);
        }
        // admin (1) + 15 users = 16 total; first page = 10
        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertOk();
        $users = $response->viewData('users');
        // paginated at 10 per page
        $this->assertCount(10, $users);
    }

    /** @test */
    public function test_users_index_can_search_by_name(): void
    {
        $admin = $this->adminUser();
        // NOTE: SQLite's LIKE does not handle non-ASCII characters the same way MySQL does,
        // so we use ASCII names here to test the search logic.
        // Arabic name searches work correctly on the production MySQL database.
        User::factory()->create(['name' => 'Ahmed Mohamed', 'userId' => 'SRCH_USR001']);
        User::factory()->create(['name' => 'Khaled Ali',   'userId' => 'SRCH_USR002']);

        $response = $this->actingAs($admin)->get(route('users.index') . '?search=Ahmed');

        $response->assertOk();
        $users = $response->viewData('users');
        $this->assertCount(1, $users);
        $this->assertEquals('Ahmed Mohamed', $users->first()->name);
    }

    /** @test */
    public function test_users_index_can_search_by_user_id(): void
    {
        $admin = $this->adminUser();
        User::factory()->create(['name' => 'أحمد',  'userId' => 'USR_ALPHA']);
        User::factory()->create(['name' => 'خالد',  'userId' => 'USR_BETA']);

        $response = $this->actingAs($admin)->get(route('users.index') . '?search=USR_BETA');

        $response->assertOk();
        $users = $response->viewData('users');
        $this->assertCount(1, $users);
        $this->assertEquals('USR_BETA', $users->first()->userId);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  create() – GET /users/create
    // ──────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_admin_can_access_create_user_page(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get(route('users.create'));

        $response->assertOk();
        $response->assertViewIs('users.create');
    }

    /** @test */
    public function test_non_admin_cannot_access_create_user_page(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->get(route('users.create'));

        $response->assertStatus(403);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  store() – POST /users
    // ──────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_admin_can_create_a_new_user(): void
    {
        $admin = $this->adminUser();

        $payload = [
            'name'     => 'مستخدم جديد',
            'email'    => 'newuser@example.com',
            'userId'   => 'UID9999',
            'password' => 'secret1234',
            'is_admin' => false,
        ];

        $response = $this->actingAs($admin)
            ->post(route('users.store'), $payload);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name'   => 'مستخدم جديد',
            'email'  => 'newuser@example.com',
            'userId' => 'UID9999',
        ]);
    }

    /** @test */
    public function test_store_hashes_the_password(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)->post(route('users.store'), [
            'name'     => 'Test Hash',
            'email'    => 'testhash@example.com',
            'userId'   => 'UID_HASH',
            'password' => 'plainpassword',
            'is_admin' => false,
        ]);

        $user = User::where('email', 'testhash@example.com')->first();
        $this->assertTrue(Hash::check('plainpassword', $user->password));
    }

    /** @test */
    public function test_store_rejects_duplicate_email(): void
    {
        $admin = $this->adminUser();
        User::factory()->create(['email' => 'exists@example.com', 'userId' => 'EX001']);

        $response = $this->actingAs($admin)
            ->from(route('users.create'))
            ->post(route('users.store'), [
                'name'     => 'Another',
                'email'    => 'exists@example.com',
                'userId'   => 'EX002',
                'password' => 'secret1234',
            ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function test_store_rejects_duplicate_user_id(): void
    {
        $admin = $this->adminUser();
        User::factory()->create(['email' => 'unique@example.com', 'userId' => 'SAME_ID']);

        $response = $this->actingAs($admin)
            ->from(route('users.create'))
            ->post(route('users.store'), [
                'name'     => 'Another',
                'email'    => 'another@example.com',
                'userId'   => 'SAME_ID',
                'password' => 'secret1234',
            ]);

        $response->assertSessionHasErrors('userId');
    }

    /** @test */
    public function test_store_rejects_missing_required_fields(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)
            ->from(route('users.create'))
            ->post(route('users.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'userId', 'password']);
    }

    /** @test */
    public function test_store_rejects_password_shorter_than_8_chars(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)
            ->from(route('users.create'))
            ->post(route('users.store'), [
                'name'     => 'Test',
                'email'    => 'shortpw@example.com',
                'userId'   => 'USR_SHORT',
                'password' => '1234567', // 7 chars
            ]);

        $response->assertSessionHasErrors('password');
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  edit() – GET /users/{user}/edit
    // ──────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_admin_can_access_edit_user_page(): void
    {
        $admin  = $this->adminUser();
        $target = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('users.edit', $target));

        $response->assertOk();
        $response->assertViewIs('users.edit');
        $response->assertViewHas('user', $target);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  update() – PUT /users/{user}
    // ──────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_admin_can_update_user_details(): void
    {
        $admin  = $this->adminUser();
        $target = User::factory()->create([
            'name'   => 'Old Name',
            'email'  => 'old@example.com',
            'userId' => 'OLD_ID',
        ]);

        $response = $this->actingAs($admin)->put(route('users.update', $target), [
            'name'   => 'New Name',
            'email'  => 'new@example.com',
            'userId' => 'NEW_ID',
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $target->refresh();
        $this->assertEquals('New Name', $target->name);
        $this->assertEquals('new@example.com', $target->email);
        $this->assertEquals('NEW_ID', $target->userId);
    }

    /** @test */
    public function test_update_with_new_password_changes_hash(): void
    {
        $admin  = $this->adminUser();
        // Use an explicit string userId so the update validation (string rule) passes
        $target = User::factory()->create(['userId' => 'UPDATE_PW_UID']);
        $oldHash = $target->password;

        $this->actingAs($admin)->put(route('users.update', $target), [
            'name'     => $target->name,
            'email'    => $target->email,
            'userId'   => $target->userId,
            'password' => 'newpassword123',
        ]);

        $target->refresh();
        $this->assertNotEquals($oldHash, $target->password);
        $this->assertTrue(Hash::check('newpassword123', $target->password));
    }

    /** @test */
    public function test_update_without_password_keeps_existing_hash(): void
    {
        $admin  = $this->adminUser();
        $target = User::factory()->create();
        $oldHash = $target->password;

        $this->actingAs($admin)->put(route('users.update', $target), [
            'name'   => $target->name,
            'email'  => $target->email,
            'userId' => $target->userId,
            // password omitted
        ]);

        $target->refresh();
        $this->assertEquals($oldHash, $target->password);
    }

    /** @test */
    public function test_update_rejects_duplicate_email_of_another_user(): void
    {
        $admin   = $this->adminUser();
        $userA   = User::factory()->create(['email' => 'a@example.com', 'userId' => 'IDXA']);
        $userB   = User::factory()->create(['email' => 'b@example.com', 'userId' => 'IDXB']);

        $response = $this->actingAs($admin)
            ->from(route('users.edit', $userB))
            ->put(route('users.update', $userB), [
                'name'   => $userB->name,
                'email'  => 'a@example.com', // duplicate of userA
                'userId' => $userB->userId,
            ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function test_update_allows_user_to_keep_their_own_email(): void
    {
        $admin  = $this->adminUser();
        $target = User::factory()->create(['email' => 'keep@example.com', 'userId' => 'KEEP_ID']);

        $response = $this->actingAs($admin)->put(route('users.update', $target), [
            'name'   => 'Updated Name',
            'email'  => 'keep@example.com', // same email, should not fail unique rule
            'userId' => 'KEEP_ID',
        ]);

        $response->assertSessionHasNoErrors();
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  destroy() – DELETE /users/{user}
    // ──────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_admin_can_delete_a_user(): void
    {
        $admin  = $this->adminUser();
        $target = User::factory()->create();

        $response = $this->actingAs($admin)->delete(route('users.destroy', $target));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    /** @test */
    public function test_non_admin_cannot_delete_a_user(): void
    {
        $user   = $this->regularUser();
        $target = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('users.destroy', $target));

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }
}
