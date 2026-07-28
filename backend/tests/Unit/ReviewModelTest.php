<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class ReviewModelTest extends TestCase
{
    public function test_review_has_the_expected_fillable_attributes(): void
    {
        $review = new Review();

        $this->assertSame([
            'user_id',
            'product_id',
            'rating',
            'title',
            'comment',
            'approved',
        ], $review->getFillable());
    }

    public function test_approved_attribute_is_cast_to_boolean(): void
    {
        $review = new Review(['approved' => 1]);

        $this->assertTrue($review->approved);
    }

    public function test_review_belongs_to_a_user(): void
    {
        $relation = (new Review())->user();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(User::class, $relation->getRelated());
    }

    public function test_review_belongs_to_a_product(): void
    {
        $relation = (new Review())->product();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Product::class, $relation->getRelated());
    }

    public function test_user_has_many_reviews(): void
    {
        $relation = (new User())->reviews();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf(Review::class, $relation->getRelated());
    }

    public function test_product_has_many_reviews(): void
    {
        $relation = (new Product())->reviews();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf(Review::class, $relation->getRelated());
    }
}
