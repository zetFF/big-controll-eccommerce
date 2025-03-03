<div class="relative bg-white rounded-lg shadow overflow-hidden">
    <!-- Wishlist Button -->
    @auth
        <button onclick="toggleWishlist({{ $product->id }})"
                class="absolute top-2 right-2 p-2 rounded-full bg-white bg-opacity-75 hover:bg-opacity-100 transition-opacity">
            <svg class="w-6 h-6 wishlist-icon-{{ $product->id }} {{ auth()->user()->hasInWishlist($product) ? 'text-red-500' : 'text-gray-400' }}"
                 fill="currentColor"
                 viewBox="0 0 24 24">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
        </button>
    @endauth

    <!-- Existing product card content -->
    ...
</div>

@push('scripts')
<script>
function toggleWishlist(productId) {
    fetch(`/wishlist/${productId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        const icon = document.querySelector(`.wishlist-icon-${productId}`);
        if (data.in_wishlist) {
            icon.classList.remove('text-gray-400');
            icon.classList.add('text-red-500');
        } else {
            icon.classList.remove('text-red-500');
            icon.classList.add('text-gray-400');
        }
    });
}
</script>
@endpush 