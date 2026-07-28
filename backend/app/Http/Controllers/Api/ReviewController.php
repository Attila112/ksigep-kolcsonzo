<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Review;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $review = Review::query()->create([
            'user_id' => $request->user()->id,
            'product_id' => $request->validated('product_id'),
            'rating' => $request->validated('rating'),
            'title' => $request->validated('title'),
            'comment' => $request->validated('comment'),
            'approved' => false,
        ]);

        return response()->json([
            'message' => 'Az értékelés sikeresen létrejött, jóváhagyásra vár.',
            'review' => $review,
        ], 201);
    }
}