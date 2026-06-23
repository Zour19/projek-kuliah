<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Florist Shop' }}</title>
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            margin: 0;
            background: #f9fafb;
            color: #111827;
            line-height: 1.5;
        }
        .page {
            max-width: 1180px;
            margin: 0 auto;
            padding: 24px;
        }
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            gap: 16px;
            flex-wrap: wrap;
        }
        nav a {
            color: #1f2937;
            text-decoration: none;
            font-weight: 600;
        }
        .hero {
            display: grid;
            grid-template-columns: 1.8fr 1fr;
            gap: 24px;
            align-items: center;
            margin-bottom: 40px;
        }
        .hero img {
            width: 100%;
            border-radius: 20px;
            display: block;
            object-fit: cover;
            min-height: 320px;
        }
        .section-title {
            margin: 0 0 18px;
            font-size: 2rem;
            letter-spacing: -0.03em;
        }
        .section-subtitle {
            margin: 0;
            color: #4b5563;
        }
        .card-grid {
            display: grid;
            gap: 18px;
        }
        .grid-3 {
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }
        .product-card,
        .category-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }
        .card-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        .card-body {
            padding: 18px;
        }
        .card-body h2 {
            margin: 0 0 10px;
            font-size: 1.15rem;
        }
        .card-body p {
            margin: 0;
            color: #4b5563;
        }
        .card-footer {
            margin-top: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .price {
            color: #111827;
            font-weight: 700;
        }
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 18px;
            border-radius: 999px;
            border: none;
            color: white;
            background: #ef4444;
            text-decoration: none;
            font-weight: 700;
        }
        .footer {
            margin-top: 48px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 0.95rem;
        }
        @media (max-width: 860px) {
            .hero {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <nav>
            <a href="{{ route('home') }}">Florist Shop</a>
            <div>
                <a href="{{ route('home') }}">Home</a>
            </div>
        </nav>

        {{ $slot }}

        <div class="footer">
            Built with Laravel and the existing florist assets.
        </div>
    </div>
</body>
</html>
