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
            ->assertJsonPath('product.price_per_day', 8000)
            ->assertJsonPath('product.deposit', 30000)
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
}
