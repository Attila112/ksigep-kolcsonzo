<?php

namespace Tests\Feature\Product;

use App\Models\Category;
use App\Models\Product;
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
}
