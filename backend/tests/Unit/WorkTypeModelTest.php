<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Models\WorkType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkTypeModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_work_type_can_have_many_products(): void
    {
        $workType = $this->createWorkType();

        $firstProduct = $this->createProduct([
            'name' => 'Betonkeverő 180L',
        ]);

        $secondProduct = $this->createProduct([
            'name' => 'Talicska',
        ]);

        $workType->products()->attach([
            $firstProduct->id,
            $secondProduct->id,
        ]);

        $this->assertCount(
            2,
            $workType->fresh()->products
        );
    }

    public function test_product_can_have_many_work_types(): void
    {
        $product = $this->createProduct();

        $firstWorkType = $this->createWorkType([
            'name' => 'Betonozás',
            'slug' => 'betonozas',
        ]);

        $secondWorkType = $this->createWorkType([
            'name' => 'Kerítés építés',
            'slug' => 'kerites-epites',
        ]);

        $product->workTypes()->attach([
            $firstWorkType->id,
            $secondWorkType->id,
        ]);

        $this->assertCount(
            2,
            $product->fresh()->workTypes
        );
    }

    public function test_active_is_cast_to_boolean(): void
    {
        $workType = $this->createWorkType([
            'active' => 1,
        ]);

        $this->assertIsBool($workType->active);
        $this->assertTrue($workType->active);
    }

    public function test_sort_order_is_cast_to_integer(): void
    {
        $workType = $this->createWorkType([
            'sort_order' => '10',
        ]);

        $this->assertIsInt($workType->sort_order);
        $this->assertSame(10, $workType->sort_order);
    }

    public function test_same_product_cannot_be_attached_twice_to_same_work_type(): void
    {
        $workType = $this->createWorkType();
        $product = $this->createProduct();

        $workType->products()->attach($product->id);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $workType->products()->attach($product->id);
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
