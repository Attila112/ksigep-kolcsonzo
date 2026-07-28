<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Category;

class ReviewTest extends TestCase
{
    use RefreshDatabase;
    private function createProduct(): Product
    {
        $category = Category::query()->create([
            'name' => 'Betonkeverők',
            'description' => 'Teszt kategória',
            'active' => true,
        ]);

        return Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Betonkeverő 180L',
            'description' => 'Teszt termék',
            'price_per_day' => 8000,
            'deposit' => 30000,
            'active' => true,
        ]);
    }

    public function test_authenticated_user_can_create_review(): void
    {
        $user = User::factory()->create([
            'active' => true,
        ]);

        $product = $this->createProduct();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Nagyon jó gép',
            'comment' => 'Könnyen használható és hibátlanul működött.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath(
                'message',
                'Az értékelés sikeresen létrejött, jóváhagyásra vár.'
            )
            ->assertJsonPath('review.user_id', $user->id)
            ->assertJsonPath('review.product_id', $product->id)
            ->assertJsonPath('review.rating', 5)
            ->assertJsonPath('review.approved', false);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Nagyon jó gép',
            'comment' => 'Könnyen használható és hibátlanul működött.',
            'approved' => false,
        ]);
    }

    public function test_guest_cannot_create_review(): void
    {
        $product = $this->createProduct();

        $response = $this->postJson('/api/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Nagyon jó gép',
            'comment' => 'Könnyen használható és hibátlanul működött.',
        ]);

        $response->assertUnauthorized();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_rating_is_required(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/reviews', [
            'product_id' => $product->id,
            'comment' => 'Teszt vélemény.',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rating');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_rating_must_be_between_one_and_five(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/reviews', [
            'product_id' => $product->id,
            'rating' => 6,
            'comment' => 'Teszt vélemény.',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rating');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_product_must_exist(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/reviews', [
            'product_id' => 999999,
            'rating' => 5,
            'comment' => 'Teszt vélemény.',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('product_id');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_comment_is_required(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('comment');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_user_cannot_review_same_product_twice(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct();

        Review::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 4,
            'title' => 'Első értékelés',
            'comment' => 'Korábbi vélemény.',
            'approved' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Második értékelés',
            'comment' => 'Újabb vélemény.',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('product_id')
            ->assertJsonPath(
                'errors.product_id.0',
                'Ezt a terméket már értékelted.'
            );

        $this->assertDatabaseCount('reviews', 1);
    }
}
