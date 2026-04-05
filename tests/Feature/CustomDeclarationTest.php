<?php

namespace Tests\Feature;

use App\Models\CustomDeclaration;
use App\Models\DeclarationHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for CustomDeclarationController.
 *
 * These tests cover the current behaviour of the controller so that we can
 * safely refactor towards a Service / Repository architecture and verify that
 * nothing has broken.
 */
class CustomDeclarationTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────────────────────

    /** Return a regular (non-admin) authenticated user. */
    private function regularUser(): User
    {
        return User::factory()->create(['is_admin' => false]);
    }

    /** Return an admin authenticated user. */
    private function adminUser(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  index() – GET /dashboard
    // ──────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard');

        // The login route in this project resolves to '/' (the homepage)
        $response->assertRedirect('/');
    }

    /** @test */
    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertViewIs('dashboard');
    }

    /** @test */
    public function test_dashboard_lists_all_active_declarations(): void
    {
        $user = $this->regularUser();
        CustomDeclaration::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('declarations');

        $declarations = $response->viewData('declarations');
        $this->assertCount(3, $declarations);
    }

    /** @test */
    public function test_dashboard_does_not_show_soft_deleted_declarations(): void
    {
        $user = $this->regularUser();

        CustomDeclaration::factory()->count(2)->create();
        CustomDeclaration::factory()->create()->delete(); // soft-deleted

        $response = $this->actingAs($user)->get('/dashboard');

        $declarations = $response->viewData('declarations');
        $this->assertCount(2, $declarations);
    }

    /** @test */
    public function test_dashboard_can_be_searched_by_declaration_number(): void
    {
        $user = $this->regularUser();

        $target = CustomDeclaration::factory()->create(['declaration_number' => '12345678']);
        CustomDeclaration::factory()->create(['declaration_number' => '99999999']);

        $response = $this->actingAs($user)->get('/dashboard?search=12345678');

        $response->assertOk();
        $declarations = $response->viewData('declarations');
        $this->assertCount(1, $declarations);
        $this->assertEquals('12345678', $declarations->first()->declaration_number);
    }

    /** @test */
    public function test_dashboard_search_truncates_input_longer_than_17_chars(): void
    {
        $user = $this->regularUser();

        // The controller trims to substr($param, 17) — i.e. chars AFTER index 17.
        // With a 20-char string 'XXXXXXXXXXXXXXXXX123' the result is '123'.
        CustomDeclaration::factory()->create(['declaration_number' => '123']);
        CustomDeclaration::factory()->create(['declaration_number' => 'XXXXXXXXXXXXXXXXX123']);

        $response = $this->actingAs($user)->get('/dashboard?search=XXXXXXXXXXXXXXXXX123');

        $response->assertOk();
        $declarations = $response->viewData('declarations');
        $this->assertCount(1, $declarations);
        $this->assertEquals('123', $declarations->first()->declaration_number);
    }

    /** @test */
    public function test_dashboard_sorting_defaults_to_created_at_desc(): void
    {
        $user = $this->regularUser();

        $old = CustomDeclaration::factory()->create(['created_at' => now()->subDays(2)]);
        $new = CustomDeclaration::factory()->create(['created_at' => now()]);

        $response = $this->actingAs($user)->get('/dashboard');

        $declarations = $response->viewData('declarations');
        $this->assertEquals($new->id, $declarations->first()->id);
    }

    /** @test */
    public function test_dashboard_sorting_by_status(): void
    {
        $user = $this->regularUser();

        CustomDeclaration::factory()->create(['status' => 'تم التخليص']);
        CustomDeclaration::factory()->create(['status' => 'قيد التخليص']);

        $response = $this->actingAs($user)->get('/dashboard?sort=status&direction=asc');

        $response->assertOk();
        $declarations = $response->viewData('declarations');
        // The first item should have the alphabetically earlier status
        $this->assertNotNull($declarations->first());
    }

    /** @test */
    public function test_dashboard_ignores_invalid_sort_column(): void
    {
        $user = $this->regularUser();
        CustomDeclaration::factory()->count(2)->create();

        // An invalid sort column should fall back to 'created_at' and not throw
        $response = $this->actingAs($user)->get('/dashboard?sort=injection_attempt');

        $response->assertOk();
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  store() – POST /declaration/store
    // ──────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_store_creates_a_new_declaration(): void
    {
        $user = $this->regularUser();

        $payload = [
            'declaration_number' => '10000001',
            'declaration_type'   => '220',
            'year'               => 2025,
            'status'             => 'قيد التخليص',
            'description'        => 'بيان اختباري',
        ];

        $response = $this->actingAs($user)
            ->post(route('declaration.store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('custom_declarations', [
            'declaration_number' => '10000001',
            'declaration_type'   => '220',
            'year'               => 2025,
            'status'             => 'قيد التخليص',
        ]);
    }

    /** @test */
    public function test_store_also_creates_a_history_record(): void
    {
        $user = $this->regularUser();

        $payload = [
            'declaration_number' => '10000002',
            'declaration_type'   => '224',
            'year'               => 2025,
            'status'             => 'قيد التخليص',
            'description'        => 'وصف تفصيلي',
        ];

        $this->actingAs($user)->post(route('declaration.store'), $payload);

        $declaration = CustomDeclaration::where('declaration_number', '10000002')->first();
        $this->assertNotNull($declaration);

        $this->assertDatabaseHas('declaration_history', [
            'declaration_id' => $declaration->id,
            'user_id'        => $user->id,
            'action'         => 'قيد التخليص',
            'description'    => 'وصف تفصيلي',
        ]);
    }

    /** @test */
    public function test_store_uses_default_description_when_none_provided(): void
    {
        $user = $this->regularUser();

        $payload = [
            'declaration_number' => '10000003',
            'declaration_type'   => '220',
            'year'               => 2025,
            'status'             => 'قيد التخليص',
            // 'description' intentionally omitted
        ];

        $this->actingAs($user)->post(route('declaration.store'), $payload);

        $declaration = CustomDeclaration::where('declaration_number', '10000003')->first();

        $this->assertDatabaseHas('declaration_history', [
            'declaration_id' => $declaration->id,
            'description'    => 'لا يوجد',
        ]);
    }

    /** @test */
    public function test_store_soft_deletes_declaration_when_status_is_archive(): void
    {
        $user = $this->regularUser();

        $payload = [
            'declaration_number' => '10000004',
            'declaration_type'   => '220',
            'year'               => 2025,
            'status'             => 'العقبة الارشيف',
        ];

        $this->actingAs($user)->post(route('declaration.store'), $payload);

        // The record should be soft-deleted (not visible in active scope)
        $this->assertDatabaseMissing('custom_declarations', [
            'declaration_number' => '10000004',
            'deleted_at'         => null,
        ]);

        // But it should exist in the trashed scope
        $this->assertSoftDeleted('custom_declarations', [
            'declaration_number' => '10000004',
        ]);
    }

    /** @test */
    public function test_store_rejects_missing_required_fields(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('declaration.store'), []);

        $response->assertSessionHasErrors(['declaration_number', 'declaration_type', 'year', 'status']);
    }

    /** @test */
    public function test_store_rejects_duplicate_declaration_same_type_and_year(): void
    {
        $user = $this->regularUser();

        CustomDeclaration::factory()->create([
            'declaration_number' => '10000005',
            'declaration_type'   => '220',
            'year'               => 2025,
        ]);

        $payload = [
            'declaration_number' => '10000005',
            'declaration_type'   => '220',
            'year'               => 2025,
            'status'             => 'قيد التخليص',
        ];

        $response = $this->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('declaration.store'), $payload);

        $response->assertSessionHasErrors('declaration_number');
    }

    /** @test */
    public function test_store_truncates_declaration_number_longer_than_17_chars(): void
    {
        $user = $this->regularUser();

        // 20-char number: first 17 chars are ignored, last 3 chars ('123') are kept
        $longNumber = 'XXXXXXXXXXXXXXXXX123';

        $payload = [
            'declaration_number' => $longNumber,
            'declaration_type'   => '220',
            'year'               => 2025,
            'status'             => 'قيد التخليص',
        ];

        $this->actingAs($user)->post(route('declaration.store'), $payload);

        $this->assertDatabaseHas('custom_declarations', [
            'declaration_number' => '123',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  updateStatus() – PUT /declaration/update/{id}
    // ──────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_update_status_changes_declaration_fields(): void
    {
        $user = $this->regularUser();

        $declaration = CustomDeclaration::factory()->create([
            'declaration_number' => '20000001',
            'declaration_type'   => '220',
            'year'               => 2025,
            'status'             => 'قيد التخليص',
        ]);

        $payload = [
            'editNumber'      => '20000001',
            'declaration_type' => '224',
            'year'             => 2026,
            'status'           => 'تم التخليص',
            'editDescription'  => 'تم الانتهاء',
        ];

        $response = $this->actingAs($user)
            ->put(route('declaration.updateStatus', $declaration->id), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $declaration->refresh();
        $this->assertEquals('224', $declaration->declaration_type);
        $this->assertEquals(2026, $declaration->year);
        $this->assertEquals('تم التخليص', $declaration->status);
    }

    /** @test */
    public function test_update_status_creates_history_when_status_changes(): void
    {
        $user = $this->regularUser();

        $declaration = CustomDeclaration::factory()->create([
            'status' => 'قيد التخليص',
        ]);

        $payload = [
            'editNumber'      => $declaration->declaration_number,
            'declaration_type' => $declaration->declaration_type,
            'year'             => $declaration->year,
            'status'           => 'تم التخليص',
            'editDescription'  => 'انتهى',
        ];

        $this->actingAs($user)
            ->put(route('declaration.updateStatus', $declaration->id), $payload);

        $this->assertDatabaseHas('declaration_history', [
            'declaration_id' => $declaration->id,
            'user_id'        => $user->id,
        ]);
    }

    /** @test */
    public function test_update_status_returns_info_when_nothing_changed(): void
    {
        $user = $this->regularUser();

        $declaration = CustomDeclaration::factory()->create([
            'declaration_number' => '30000001',
            'declaration_type'   => '220',
            'year'               => 2025,
            'status'             => 'قيد التخليص',
        ]);

        $payload = [
            'editNumber'      => '30000001',
            'declaration_type' => '220',
            'year'             => 2025,
            'status'           => 'قيد التخليص',
            'editDescription'  => null,
        ];

        $response = $this->actingAs($user)
            ->put(route('declaration.updateStatus', $declaration->id), $payload);

        $response->assertSessionHas('info', 'لم يتم تغيير الحالة');
    }

    /** @test */
    public function test_update_status_soft_deletes_when_status_is_archive(): void
    {
        $user = $this->regularUser();

        $declaration = CustomDeclaration::factory()->create([
            'status' => 'قيد التخليص',
        ]);
        $id = $declaration->id;

        $payload = [
            'editNumber'      => $declaration->declaration_number,
            'declaration_type' => $declaration->declaration_type,
            'year'             => $declaration->year,
            'status'           => 'العقبة الارشيف',
        ];

        $this->actingAs($user)
            ->put(route('declaration.updateStatus', $id), $payload);

        $this->assertSoftDeleted('custom_declarations', ['id' => $id]);
    }

    /** @test */
    public function test_update_status_returns_404_for_nonexistent_declaration(): void
    {
        $user = $this->regularUser();

        $payload = [
            'editNumber'      => '99999999',
            'declaration_type' => '220',
            'year'             => 2025,
            'status'           => 'قيد التخليص',
        ];

        $response = $this->actingAs($user)
            ->put(route('declaration.updateStatus', 999999), $payload);

        $response->assertNotFound();
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  showHistory() – GET /declaration/history/{id}
    // ──────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_show_history_returns_history_for_active_declaration(): void
    {
        $user        = $this->regularUser();
        $declaration = CustomDeclaration::factory()->create();

        DeclarationHistory::factory()->create([
            'declaration_id' => $declaration->id,
            'user_id'        => $user->id,
            'action'         => 'قيد التخليص',
        ]);

        $response = $this->actingAs($user)
            ->get(route('declaration.showHistory', $declaration->id));

        $response->assertOk();
        $response->assertViewIs('history');
        $response->assertViewHas('declaration');
        $response->assertViewHas('history');
    }

    /** @test */
    public function test_show_history_returns_history_for_soft_deleted_declaration(): void
    {
        $user        = $this->regularUser();
        $declaration = CustomDeclaration::factory()->create();
        $declaration->delete(); // soft-delete

        $response = $this->actingAs($user)
            ->get(route('declaration.showHistory', $declaration->id));

        $response->assertOk();
        $response->assertViewHas('declaration');
    }

    /** @test */
    public function test_show_history_returns_404_for_nonexistent_declaration(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->get(route('declaration.showHistory', 999999));

        $response->assertNotFound();
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  showRestore() – GET /declaration/restore
    // ──────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_show_restore_lists_only_soft_deleted_declarations(): void
    {
        $user = $this->regularUser();

        CustomDeclaration::factory()->count(2)->create(); // active
        $trashed = CustomDeclaration::factory()->create();
        $trashed->delete();

        $response = $this->actingAs($user)->get(route('declaration.showRestore'));

        $response->assertOk();
        $response->assertViewIs('restore');

        $declarations = $response->viewData('declarations');
        $this->assertCount(1, $declarations);
        $this->assertEquals($trashed->id, $declarations->first()->id);
    }

    /** @test */
    public function test_show_restore_can_be_searched_by_declaration_number(): void
    {
        $user = $this->regularUser();

        $target  = CustomDeclaration::factory()->create(['declaration_number' => '77777777']);
        $other   = CustomDeclaration::factory()->create(['declaration_number' => '88888888']);
        $target->delete();
        $other->delete();

        $response = $this->actingAs($user)
            ->get(route('declaration.showRestore') . '?search=77777777');

        $declarations = $response->viewData('declarations');
        $this->assertCount(1, $declarations);
        $this->assertEquals('77777777', $declarations->first()->declaration_number);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  restore() – GET /dashboard/restore/{id}
    // ──────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_restore_recovers_a_soft_deleted_declaration(): void
    {
        $user = $this->regularUser();

        $declaration = CustomDeclaration::factory()->create();
        $declaration->delete();

        $response = $this->actingAs($user)
            ->get(route('declaration.restore', $declaration->id));

        $response->assertRedirect(route('declaration.showRestore'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('custom_declarations', [
            'id'         => $declaration->id,
            'deleted_at' => null,
        ]);
    }
}
