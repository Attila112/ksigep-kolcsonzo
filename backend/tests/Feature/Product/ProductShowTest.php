<?php

namespace Tests\Feature\Product;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_details_endpoint_returns_active_product(): void
    {
        $category = $this->createCategory();

        $product = $this->createProduct($category, [
            'name' => 'Betonkeverő 180L',
            'description' => 'Professzionális betonkeverő.',
            'price_per_day' => 8000,
            'deposit' => 30000,
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response
            ->assertOk()
            ->assertJsonPath('product.id', $product->id)
            ->assertJsonPath('product.name', 'Betonkeverő 180L')
            ->assertJsonPath(
                'product.description',
                'Professzionális betonkeverő.'
            )
            ->assertJsonPath('product.price_per_day', '8000.00')
            ->assertJsonPath('product.deposit', '30000.00')
            ->assertJsonPath('product.category.id', $category->id)
            ->assertJsonPath('product.category.name', $category->name);
    }

    public function test_product_details_include_only_approved_review_statistics(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $pendingUser = User::factory()->create();

        $this->createReview($firstUser, $product, [
            'rating' => 4,
            'approved' => true,
        ]);

        $this->createReview($secondUser, $product, [
            'rating' => 5,
            'approved' => true,
        ]);

        $this->createReview($pendingUser, $product, [
            'rating' => 1,
            'approved' => false,
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response
            ->assertOk()
            ->assertJsonPath('product.reviews_count', 2)
            ->assertJsonPath('product.average_rating', 4.5);
    }

    public function test_inactive_product_is_not_publicly_available(): void
    {
        $category = $this->createCategory();

        $product = $this->createProduct($category, [
            'active' => false,
        ]);

        $this->getJson("/api/products/{$product->id}")
            ->assertNotFound();
    }

    public function test_product_from_inactive_category_is_not_publicly_available(): void
    {
        $category = $this->createCategory([
            'active' => false,
        ]);

        $product = $this->createProduct($category);

        $this->getJson("/api/products/{$product->id}")
            ->assertNotFound();
    }

    public function test_missing_product_returns_not_found(): void
    {
        $this->getJson('/api/products/999999')
            ->assertNotFound();
    }

    private function createCategory(array $attributes = []): Category
    {
        return Category::query()->create(array_merge([
            'name' => 'Teszt kategória',
            'description' => 'Teszt kategória leírása',
            'active' => true,
        ], $attributes));
    }

    private function createProduct(
        Category $category,
        array $attributes = []
    ): Product {
        return Product::query()->create(array_merge([
            'category_id' => $category->id,
            'name' => 'Teszt termék',
            'description' => 'Teszt termékleírás',
            'price_per_day' => 8000,
            'deposit' => 30000,
            'active' => true,
        ], $attributes));
    }

    private function createReview(
        User $user,
        Product $product,
        array $attributes = []
    ): Review {
        return Review::query()->create(array_merge([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Teszt értékelés',
            'comment' => 'Teszt vélemény.',
            'approved' => true,
        ], $attributes));
    }
    public function test_product_detail_returns_battery_requirements(): void
    {
        $batterySystem = \App\Models\BatterySystem::factory()
            ->makita()
            ->create();

        $category = \App\Models\Category::query()->create([
            'name' => 'Kerti gépek',
            'description' => 'Teszt kategória',
            'active' => true,
        ]);

        $product = \App\Models\Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Akkumulátoros szegélynyíró',
            'sku' => 'TEST-SZEGELYNYIRO',
            'inventory_prefix' => 'TSZ',
            'battery_system_id' => $batterySystem->id,
            'required_batteries' => 2,
            'required_chargers' => 1,
            'description' => 'Teszt termék.',
            'price_per_day' => 8000,
            'deposit' => 20000,
            'active' => true,
        ]);

        $response = $this->getJson(
            "/api/products/{$product->id}"
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'product.battery_system.id',
                $batterySystem->id
            )
            ->assertJsonPath(
                'product.battery_system.manufacturer',
                'Makita'
            )
            ->assertJsonPath(
                'product.battery_system.name',
                'LXT 18V'
            )
            ->assertJsonPath(
                'product.battery_system.voltage',
                '18.00'
            )
            ->assertJsonPath(
                'product.required_batteries',
                2
            )
            ->assertJsonPath(
                'product.required_chargers',
                1
            );
    }
    public function test_product_detail_returns_no_battery_system_for_non_battery_product(): void
    {
        $category = \App\Models\Category::query()->create([
            'name' => 'Kerti gépek',
            'description' => 'Teszt kategória',
            'active' => true,
        ]);

        $product = \App\Models\Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Benzines fűnyíró',
            'sku' => 'TEST-FUNYIRO',
            'inventory_prefix' => 'TFU',
            'description' => 'Teszt termék.',
            'price_per_day' => 6000,
            'deposit' => 10000,
            'active' => true,
        ]);

        $response = $this->getJson(
            "/api/products/{$product->id}"
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
    }
}
