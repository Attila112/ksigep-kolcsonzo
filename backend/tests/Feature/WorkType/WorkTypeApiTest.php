<?php

namespace Tests\Feature\WorkType;

use App\Models\Category;
use App\Models\Product;
use App\Models\WorkType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkTypeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_work_types_endpoint_returns_only_active_work_types(): void
    {
        $activeWorkType = $this->createWorkType([
            'name' => 'Betonozás',
            'slug' => 'betonozas',
            'active' => true,
            'sort_order' => 2,
        ]);

        $this->createWorkType([
            'name' => 'Inaktív munkatípus',
            'slug' => 'inaktiv-munkatipus',
            'active' => false,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/work-types');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'work_types')
            ->assertJsonPath(
                'work_types.0.id',
                $activeWorkType->id
            )
            ->assertJsonPath(
                'work_types.0.name',
                'Betonozás'
            )
            ->assertJsonPath(
                'work_types.0.slug',
                'betonozas'
            )
            ->assertJsonPath(
                'work_types.0.icon_key',
                $activeWorkType->icon_key
            );
    }

    public function test_work_types_are_ordered_by_sort_order(): void
    {
        $laterWorkType = $this->createWorkType([
            'name' => 'Betonozás',
            'slug' => 'betonozas',
            'sort_order' => 20,
        ]);

        $earlierWorkType = $this->createWorkType([
            'name' => 'Takarítás',
            'slug' => 'takaritas',
            'sort_order' => 10,
        ]);

        $response = $this->getJson('/api/work-types');

        $response
            ->assertOk()
            ->assertJsonPath(
                'work_types.0.id',
                $earlierWorkType->id
            )
            ->assertJsonPath(
                'work_types.1.id',
                $laterWorkType->id
            );
    }

    public function test_work_type_products_endpoint_returns_active_products(): void
    {
        $workType = $this->createWorkType([
            'slug' => 'betonozas',
        ]);

        $activeProduct = $this->createProduct([
            'name' => 'Betonkeverő 180L',
            'active' => true,
        ]);

        $inactiveProduct = $this->createProduct([
            'name' => 'Inaktív termék',
            'active' => false,
        ]);

        $workType->products()->attach([
            $activeProduct->id,
            $inactiveProduct->id,
        ]);

        $response = $this->getJson(
            '/api/work-types/betonozas/products'
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'work_type.id',
                $workType->id
            )
            ->assertJsonPath(
                'work_type.slug',
                'betonozas'
            )
            ->assertJsonCount(1, 'products')
            ->assertJsonPath(
                'products.0.id',
                $activeProduct->id
            )
            ->assertJsonPath(
                'products.0.name',
                'Betonkeverő 180L'
            );
    }

    public function test_work_type_products_endpoint_excludes_products_from_inactive_category(): void
    {
        $workType = $this->createWorkType([
            'slug' => 'betonozas',
        ]);

        $inactiveCategory = Category::query()->create([
            'name' => 'Inaktív kategória',
            'description' => 'Teszt kategória',
            'active' => false,
        ]);

        $product = Product::query()->create([
            'category_id' => $inactiveCategory->id,
            'name' => 'Betonkeverő',
            'description' => 'Teszt termék',
            'price_per_day' => 8000,
            'deposit' => 30000,
            'active' => true,
        ]);

        $workType->products()->attach($product->id);

        $this->getJson(
            '/api/work-types/betonozas/products'
        )
            ->assertOk()
            ->assertJsonCount(0, 'products');
    }

    public function test_inactive_work_type_is_not_publicly_available(): void
    {
        $workType = $this->createWorkType([
            'slug' => 'betonozas',
            'active' => false,
        ]);

        $this->getJson(
            "/api/work-types/{$workType->slug}/products"
        )->assertNotFound();
    }

    public function test_missing_work_type_returns_not_found(): void
    {
        $this->getJson(
            '/api/work-types/nem-letezo/products'
        )->assertNotFound();
    }

    private function createWorkType(
        array $attributes = []
    ): WorkType {
        return WorkType::query()->create(array_merge([
            'name' => 'Betonozás',
            'slug' => 'betonozas-' . uniqid(),
            'description' => 'Betonozási munkákhoz ajánlott gépek.',
            'icon_key' => 'concrete',
            'active' => true,
            'sort_order' => 1,
        ], $attributes));
    }

    private function createProduct(
        array $attributes = []
    ): Product {
        $category = Category::query()->create([
            'name' => 'Kisgépek-' . uniqid(),
            'description' => 'Teszt kategória',
            'active' => true,
        ]);

        return Product::query()->create(array_merge([
            'category_id' => $category->id,
            'name' => 'Betonkeverő',
            'description' => 'Teszt termék',
            'price_per_day' => 8000,
            'deposit' => 30000,
            'active' => true,
        ], $attributes));
    }
}
