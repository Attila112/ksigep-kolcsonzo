<?php

namespace Tests\Feature\Product;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductListTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_list_returns_only_active_products(): void
    {
        $category = $this->createCategory();

        $activeProduct = $this->createProduct($category, [
            'name' => 'Aktív betonkeverő',
            'active' => true,
        ]);

        $inactiveProduct = $this->createProduct($category, [
            'name' => 'Inaktív betonkeverő',
            'active' => false,
        ]);

        $response = $this->getJson('/api/products');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.id', $activeProduct->id)
            ->assertJsonPath('products.0.name', 'Aktív betonkeverő');

        $response->assertJsonMissing([
            'id' => $inactiveProduct->id,
        ]);
    }

    public function test_product_list_contains_category_data(): void
    {
        $category = $this->createCategory([
            'name' => 'Betonkeverők',
        ]);

        $product = $this->createProduct($category);

        $response = $this->getJson('/api/products');

        $response
            ->assertOk()
            ->assertJsonPath('products.0.id', $product->id)
            ->assertJsonPath('products.0.category.id', $category->id)
            ->assertJsonPath(
                'products.0.category.name',
                'Betonkeverők'
            );
    }

    public function test_product_list_returns_empty_array_when_no_active_products_exist(): void
    {
        $category = $this->createCategory();

        $this->createProduct($category, [
            'active' => false,
        ]);

        $response = $this->getJson('/api/products');

        $response
            ->assertOk()
            ->assertExactJson([
                'products' => [],
            ]);
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
            'name' => 'Betonkeverő 180L',
            'description' => 'Teszt termék',
            'price_per_day' => 8000,
            'deposit' => 30000,
            'active' => true,
        ], $attributes));
    }
    public function test_product_list_does_not_return_products_from_inactive_categories(): void
    {
        $activeCategory = $this->createCategory([
            'name' => 'Aktív kategória',
            'active' => true,
        ]);

        $inactiveCategory = $this->createCategory([
            'name' => 'Inaktív kategória',
            'active' => false,
        ]);

        $visibleProduct = $this->createProduct($activeCategory, [
            'name' => 'Megjelenő termék',
        ]);

        $hiddenProduct = $this->createProduct($inactiveCategory, [
            'name' => 'Elrejtett termék',
        ]);

        $response = $this->getJson('/api/products');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.id', $visibleProduct->id);

        $response->assertJsonMissing([
            'id' => $hiddenProduct->id,
        ]);
    }

    public function test_product_list_contains_only_approved_review_statistics(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $pendingUser = User::factory()->create();

        Review::query()->create([
            'user_id' => $firstUser->id,
            'product_id' => $product->id,
            'rating' => 4,
            'title' => 'Jó értékelés',
            'comment' => 'Jóváhagyott értékelés.',
            'approved' => true,
        ]);

        Review::query()->create([
            'user_id' => $secondUser->id,
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Kiváló értékelés',
            'comment' => 'Szintén jóváhagyott.',
            'approved' => true,
        ]);

        Review::query()->create([
            'user_id' => $pendingUser->id,
            'product_id' => $product->id,
            'rating' => 1,
            'title' => 'Függő értékelés',
            'comment' => 'Ez nem számíthat bele.',
            'approved' => false,
        ]);

        $response = $this->getJson('/api/products');

        $response
            ->assertOk()
            ->assertJsonPath('products.0.reviews_count', 2)
            ->assertJsonPath('products.0.average_rating', 4.5);
    }

    public function test_product_without_approved_reviews_has_empty_review_statistics(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $user = User::factory()->create();

        Review::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Jóváhagyásra vár',
            'comment' => 'Ez még nem publikus.',
            'approved' => false,
        ]);

        $response = $this->getJson('/api/products');

        $response
            ->assertOk()
            ->assertJsonPath('products.0.id', $product->id)
            ->assertJsonPath('products.0.reviews_count', 0)
            ->assertJsonPath('products.0.average_rating', null);
    }
}
