<!-- Add this section after product details -->
<div class="mt-16">
    <h2 class="text-2xl font-bold mb-8">Customer Reviews</h2>

    <!-- Review Summary -->
    <div class="flex items-center mb-8">
        <div class="flex-1">
            <div class="flex items-center">
                <div class="text-4xl font-bold">{{ number_format($product->average_rating, 1) }}</div>
                <div class="ml-2">
                    <div class="flex items-center">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg class="w-5 h-5 {{ $i <= round($product->average_rating) ? 'text-yellow-400' : 'text-gray-300' }}"
                                 fill="currentColor"
                                 viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    <div class="text-sm text-gray-500">Based on {{ $product->reviews_count }} reviews</div>
                </div>
            </div>
        </div>

        @auth
            @if(!$product->reviews()->where('user_id', auth()->id())->exists())
                <button onclick="document.getElementById('review-form').classList.remove('hidden')"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Write a Review
                </button>
            @endif
        @endauth
    </div>

    <!-- Review Form -->
    <form id="review-form" 
          action="{{ route('products.reviews.store', $product) }}" 
          method="POST" 
          class="hidden mb-8 bg-gray-50 p-6 rounded-lg">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
            <div class="flex items-center">
                @for ($i = 1; $i <= 5; $i++)
                    <input type="radio" 
                           name="rating" 
                           value="{{ $i }}" 
                           id="rating-{{ $i }}"
                           class="hidden peer"
                           required>
                    <label for="rating-{{ $i }}"
                           class="cursor-pointer p-1">
                        <svg class="w-8 h-8 peer-checked:text-yellow-400 text-gray-300 hover:text-yellow-400"
                             fill="currentColor"
                             viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </label>
                @endfor
            </div>
        </div>
        <div class="mb-4">
            <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Review</label>
            <textarea name="comment"
                      id="comment"
                      rows="4"
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      placeholder="Write your review here..."></textarea>
        </div>
        <div class="flex justify-end gap-2">
            <button type="button"
                    onclick="document.getElementById('review-form').classList.add('hidden')"
                    class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Cancel
            </button>
            <button type="submit"
                    class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                Submit Review
            </button>
        </div>
    </form>

    <!-- Reviews List -->
    <div class="space-y-8">
        @foreach($product->reviews()->with('user')->latest()->get() as $review)
            <div class="flex">
                <div class="flex-shrink-0">
                    <div class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-gray-500 text-white">
                        {{ strtoupper(substr($review->user->name, 0, 1)) }}
                    </div>
                </div>
                <div class="ml-4 flex-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-medium">{{ $review->user->name }}</div>
                            <div class="flex items-center mt-1">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}"
                                         fill="currentColor"
                                         viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $review->created_at->format('M d, Y') }}
                        </div>
                    </div>
                    @if($review->is_verified_purchase)
                        <div class="mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                Verified Purchase
                            </span>
                        </div>
                    @endif
                    <div class="mt-2 text-gray-700">
                        {{ $review->comment }}
                    </div>
                    @if(auth()->id() === $review->user_id || auth()->user()?->is_admin)
                        <div class="mt-2 flex gap-2">
                            <form action="{{ route('reviews.destroy', $review) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-sm text-red-600 hover:text-red-900"
                                        onclick="return confirm('Are you sure you want to delete this review?')">
                                    Delete
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Related Products -->
@if($relatedProducts->isNotEmpty())
    <div class="mt-16">
        <h2 class="text-2xl font-bold mb-8">Related Products</h2>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($relatedProducts as $relatedProduct)
                <x-product-card :product="$relatedProduct" />
            @endforeach
        </div>
    </div>
@endif

<!-- Recommended Products -->
@if($recommendedProducts->isNotEmpty())
    <div class="mt-16">
        <h2 class="text-2xl font-bold mb-8">Customers Also Bought</h2>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($recommendedProducts as $recommendedProduct)
                <x-product-card :product="$recommendedProduct" />
            @endforeach
        </div>
    </div>
@endif 