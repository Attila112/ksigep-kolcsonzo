<?php

namespace Tests\Feature\Admin\Product;

use App\Models\BatterySystem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminProductUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_product_basic_information(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        Sanctum::actingAs($admin);

        $oldCategory = Category::factory()->create();

        $newCategory = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $oldCategory->id,
            'name' => 'Régi terméknév',
            'description' => 'Régi leírás.',
            'price_per_day' => 5000,
            'deposit' => 10000,
            'active' => true,
        ]);

        $response = $this->patchJson(
            "/api/admin/products/{$product->id}",
            [
                'category_id' => $newCategory->id,
                'name' => 'Új terméknév',
                'description' => 'Új termékleírás.',
                'price_per_day' => 7500,
                'deposit' => 15000,
                'active' => false,
                'battery_system_id' => null,
                'required_batteries' => 0,
                'required_chargers' => 0,
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'product.id',
                $product->id
            )
            ->assertJsonPath(
                'product.name',
                'Új terméknév'
            )
            ->assertJsonPath(
                'product.description',
                'Új termékleírás.'
            )
            ->assertJsonPath(
                'product.category.id',
                $newCategory->id
            )
            ->assertJsonPath(
                'product.price_per_day',
                '7500.00'
            )
            ->assertJsonPath(
                'product.deposit',
                '15000.00'
            )
            ->assertJsonPath(
                'product.active',
                false
            );

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'category_id' => $newCategory->id,
            'name' => 'Új terméknév',
            'description' => 'Új termékleírás.',
            'price_per_day' => 7500,
            'deposit' => 15000,
            'active' => false,
        ]);
    }

    public function test_admin_can_assign_battery_system_to_product(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        Sanctum::actingAs($admin);

        $category = Category::factory()->create();

        $batterySystem = BatterySystem::factory()
            ->makita()
            ->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'battery_system_id' => null,
            'required_batteries' => 0,
            'required_chargers' => 0,
        ]);

        $response = $this->patchJson(
            "/api/admin/products/{$product->id}",
            [
                'category_id' => $category->id,
                'name' => $product->name,
                'description' => $product->description,
                'price_per_day' => 6000,
                'deposit' => 10000,
                'active' => true,
                'battery_system_id' => $batterySystem->id,
                'required_batteries' => 2,
                'required_chargers' => 1,
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'product.battery_system.id',
                $batterySystem->id
            )
            ->assertJsonPath(
                'product.required_batteries',
                2
            )
            ->assertJsonPath(
                'product.required_chargers',
                1
            );

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'battery_system_id' => $batterySystem->id,
            'required_batteries' => 2,
            'required_chargers' => 1,
        ]);
    }

    public function test_product_without_battery_system_has_zero_battery_requirements(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        Sanctum::actingAs($admin);

        $category = Category::factory()->create();

        $batterySystem = BatterySystem::factory()
            ->makita()
            ->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'battery_system_id' => $batterySystem->id,
            'required_batteries' => 2,
            'required_chargers' => 1,
        ]);

        $response = $this->patchJson(
            "/api/admin/products/{$product->id}",
            [
                'category_id' => $category->id,
                'name' => $product->name,
                'description' => $product->description,
                'price_per_day' => 6000,
                'deposit' => 10000,
                'active' => true,

                /*
                 * Ha nincs akkumulátorrendszer,
                 * a backendnek nulláznia kell
                 * a battery requirement mezőket.
                 */
                'battery_system_id' => null,
                'required_batteries' => 2,
                'required_chargers' => 1,
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'product.battery_system',
                null
            )
            ->assertJsonPath(
                'product.required_batteries',
                0
            )
            ->assertJsonPath(
                'product.required_chargers',
                0
            );

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'battery_system_id' => null,
            'required_batteries' => 0,
            'required_chargers' => 0,
        ]);
    }

    public function test_product_update_validates_required_fields(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        Sanctum::actingAs($admin);

        $product = Product::factory()->create();

        $response = $this->patchJson(
            "/api/admin/products/{$product->id}",
            [
                'category_id' => null,
                'name' => '',
                'description' => '',
                'price_per_day' => null,
                'deposit' => null,
                'active' => null,
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'category_id',
                'name',
                'description',
                'price_per_day',
                'deposit',
                'active',
            ]);
    }

    public function test_product_update_validates_battery_system_exists(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        Sanctum::actingAs($admin);

        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->patchJson(
            "/api/admin/products/{$product->id}",
            [
                'category_id' => $category->id,
                'name' => $product->name,
                'description' => $product->description,
                'price_per_day' => 5000,
                'deposit' => 10000,
                'active' => true,
                'battery_system_id' => 999999,
                'required_batteries' => 2,
                'required_chargers' => 1,
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'battery_system_id',
            ]);
    }

    public function test_customer_cannot_update_product(): void
    {
        $customer = User::factory()
            ->customer()
            ->create();

        Sanctum::actingAs($customer);

        $product = Product::factory()->create();

        $this->patchJson(
            "/api/admin/products/{$product->id}",
            [
                'name' => 'Tiltott módosítás',
            ]
        )
            ->assertForbidden();

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
            'name' => 'Tiltott módosítás',
        ]);
    }

    public function test_guest_cannot_update_product(): void
    {
        $product = Product::factory()->create();

        $this->patchJson(
            "/api/admin/products/{$product->id}",
            [
                'name' => 'Tiltott módosítás',
            ]
        )
            ->assertUnauthorized();
    }

    public function test_admin_receives_not_found_when_updating_missing_product(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        Sanctum::actingAs($admin);

        $category = Category::factory()->create();

        $this->patchJson(
            '/api/admin/products/999999',
            [
                'category_id' => $category->id,
                'name' => 'Nem létező termék',
                'description' => 'Teszt.',
                'price_per_day' => 5000,
                'deposit' => 10000,
                'active' => true,
                'battery_system_id' => null,
                'required_batteries' => 0,
                'required_chargers' => 0,
            ]
        )
            ->assertNotFound();
    }
}