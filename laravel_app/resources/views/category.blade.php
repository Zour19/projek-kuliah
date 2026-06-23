@php
    $title = $category['name'];
@endphp

<x-layouts.app>
    <div>
        <section style="margin-bottom: 32px;">
            <h1 class="section-title">{{ $category['name'] }}</h1>
            <p class="section-subtitle">{{ $category['description'] }}</p>
        </section>

        <section>
            <div class="card-grid grid-3">
                @forelse ($products as $product)
                    <article class="product-card">
                        <img class="card-image" src="{{ asset($product['image']) }}" alt="{{ $product['name'] }}" />
                        <div class="card-body">
                            <h2>{{ $product['name'] }}</h2>
                            <p>{{ $product['description'] }}</p>
                            <div class="card-footer">
                                <span class="price">Rp {{ number_format($product['price'], 0, ',', '.') }}</span>
                                <a class="button" href="#">Order now</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <p>No products are available in this category yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
