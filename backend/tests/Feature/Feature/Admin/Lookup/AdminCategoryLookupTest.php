<?php

namespace Tests\Feature\Admin\Lookup;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminCategoryLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_categories_for_lookup(): void
    {
        $admin = User::factory()->admin()->create();

        Sanctum::actingAs($admin);

        Category::factory()->create([
            'name' => 'Kerti gépek',
            'active' => true,
        ]);

        Category::factory()->create([
            'name' => 'Takarítógépek',
            'active' => true,
        ]);

        $response = $this->getJson('/api/admin/categories');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'categories')
            ->assertJsonPath(
                'categories.0.name',
                'Kerti gépek'
            );
    }

    public function test_admin_category_lookup_includes_inactive_categories(): void
    {
        $admin = User::factory()->admin()->create();

        Sanctum::actingAs($admin);

        Category::factory()->inactive()->create();

        $this->getJson('/api/admin/categories')
            ->assertOk()
            ->assertJsonCount(1, 'categories');
    }

    public function test_customer_cannot_access_category_lookup(): void
    {
        $customer = User::factory()->customer()->create();

        Sanctum::actingAs($customer);

        $this->getJson('/api/admin/categories')
            ->assertForbidden();
    }

    public function test_guest_cannot_access_category_lookup(): void
    {
        $this->getJson('/api/admin/categories')
            ->assertUnauthorized();
    }
}