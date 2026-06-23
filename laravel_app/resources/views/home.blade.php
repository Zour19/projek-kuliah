@php
    $title = 'Home';
@endphp

<x-layouts.app>
    <div>
        <section class="hero">
            <div>
                <h1 class="section-title">Modern Florist Store</h1>
                <p class="section-subtitle">Browse handcrafted bouquets, premium standing flowers, bloom boxes, and floral accessories built from the existing project assets.</p>
                <div style="margin-top: 24px; display:flex; gap:12px; flex-wrap:wrap;">
                    @foreach ($categories as $category)
                        <a class="button" href="{{ route('category.show', ['slug' => $category['slug']]) }}">{{ $category['name'] }}</a>
                    @endforeach
                </div>
            </div>
            <img src="{{ asset('assets/hero.png') }}" alt="Florist hero image" />
        </section>

        <section style="margin-bottom: 36px;">
            <h2 class="section-title">Shop Categories</h2>
            <p class="section-subtitle">A quick way to explore the floral collections we have available.</p>
            <div class="card-grid grid-3" style="margin-top: 22px;">
                @foreach ($categories as $category)
                    <article class="category-card">
                        <img class="card-image" src="{{ asset($category['image']) }}" alt="{{ $category['name'] }}" />
                        <div class="card-body">
                            <h2>{{ $category['name'] }}</h2>
                            <p>{{ $category['description'] }}</p>
                            <div class="card-footer">
                                <span class="price">View collection</span>
                                <a class="button" href="{{ route('category.show', ['slug' => $category['slug']]) }}">Explore</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section>
            <h2 class="section-title">Featured Products</h2>
            <p class="section-subtitle">Popular items from our floral collections.</p>
            <div class="card-grid grid-3" style="margin-top: 22px;">
                @foreach ($featuredProducts as $product)
                    <article class="product-card">
                        <img class="card-image" src="{{ asset($product['image']) }}" alt="{{ $product['name'] }}" />
                        <div class="card-body">
                            <h2>{{ $product['name'] }}</h2>
                            <p>{{ $product['description'] }}</p>
                            <div class="card-footer">
                                <span class="price">Rp {{ number_format($product['price'], 0, ',', '.') }}</span>
                                <span style="color:#6b7280; font-weight:600;">{{ ucfirst(str_replace('-', ' ', $product['category'])) }}</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
</x-layouts.app>
