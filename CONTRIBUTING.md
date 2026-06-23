# 🤝 Contributing Guide

## Getting Started

Thank you for your interest in contributing to Florist Shop! This guide will help you get started.

### Prerequisites
- PHP 8.3+
- Node.js 18+
- Composer
- Git
- Docker (optional, but recommended)

### Setting Up Development Environment

```bash
# 1. Fork and clone repository
git clone https://github.com/your-username/florist-shop.git
cd florist-shop

# 2. Setup Laravel app
cd laravel_app
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed

# 3. Setup frontend
npm install
npm run dev

# 4. Start development server
php artisan serve

# 5. In another terminal, watch assets
npm run dev
```

### Docker Setup (Alternative)

```bash
# Build and start
docker-compose up -d
docker-compose exec app composer install
docker-compose exec app php artisan migrate --seed
docker-compose exec app npm run dev
```

## Workflow

### 1. Create Feature Branch

```bash
# Update main branch
git checkout main
git pull origin main

# Create feature branch
git checkout -b feature/your-feature-name

# Or for bug fixes
git checkout -b fix/bug-description
```

**Branch Naming Convention:**
- `feature/` - New features
- `fix/` - Bug fixes
- `refactor/` - Code refactoring
- `docs/` - Documentation updates
- `test/` - Test additions

### 2. Make Changes

#### Code Style

Follow Laravel and PHP conventions:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Use type hints
    public function index(Request $request): JsonResponse
    {
        // Use early returns
        if (!$request->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Use meaningful variable names
        $products = Product::active()
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return response()->json($products);
    }
}
```

#### Blade Template Style

```blade
{{-- Use descriptive component names --}}
<x-layouts.app title="Page Title">
    {{-- Use proper indentation (4 spaces) --}}
    <div class="container">
        @forelse($products as $product)
            <x-product-card :product="$product" />
        @empty
            <p class="text-center">No products found</p>
        @endforelse
    </div>
</x-layouts.app>
```

#### CSS/Tailwind

```css
/* Use Tailwind utilities */
<div class="flex items-center justify-between p-4 bg-white rounded-lg shadow">
    <h1 class="text-2xl font-bold text-gray-900">Title</h1>
    <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Button
    </button>
</div>
```

### 3. Testing

Always write tests for new features:

```php
// tests/Feature/ProductApiTest.php
namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products()
    {
        Product::factory(3)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    public function test_can_create_product()
    {
        $response = $this->postJson('/api/v1/products', [
            'name' => 'Test Product',
            'price' => 100000,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
    }
}
```

**Run Tests:**

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ProductApiTest.php

# Run with coverage
php artisan test --coverage

# Run in Docker
docker-compose exec app php artisan test
```

### 4. Documentation

Update documentation if needed:

- **API Changes:** Update [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- **New Features:** Update [README.md](README.md)
- **Setup Changes:** Update [DEPLOYMENT.md](DEPLOYMENT.md)
- **Code Examples:** Add to relevant feature documentation

### 5. Commit Message

Use clear, descriptive commit messages:

```bash
# Good commit messages
git commit -m "feat: add product search API endpoint"
git commit -m "fix: resolve order status calculation bug"
git commit -m "docs: update deployment guide for SSL setup"
git commit -m "test: add comprehensive order validation tests"
git commit -m "refactor: improve product query performance with eager loading"

# Avoid vague messages
# ❌ git commit -m "fix stuff"
# ❌ git commit -m "update"
# ❌ git commit -m "changes"
```

**Commit Format:**
```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**
- `feat` - New feature
- `fix` - Bug fix
- `refactor` - Code refactoring
- `test` - Test additions
- `docs` - Documentation
- `style` - Code style changes
- `chore` - Build/tooling changes

**Example:**
```
feat(api): add advanced product filtering

- Add category filter support
- Add price range filter
- Add search functionality
- Update API documentation

Closes #123
```

### 6. Keep Your Branch Updated

```bash
# Fetch latest changes
git fetch origin

# Rebase on main
git rebase origin/main

# If conflicts occur, resolve them
git add .
git rebase --continue
```

### 7. Push and Create Pull Request

```bash
# Push branch
git push origin feature/your-feature-name

# Create PR on GitHub
# - Fill in the PR template
# - Link related issues
# - Add screenshot/demo if UI changes
# - Request reviewers
```

**PR Template:**

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] New feature
- [ ] Bug fix
- [ ] Breaking change

## Related Issues
Closes #123

## Changes Made
- Change 1
- Change 2
- Change 3

## Testing
- [ ] Tested on local development
- [ ] Added/updated tests
- [ ] All tests passing

## Screenshots (if applicable)
[Add screenshots/GIFs here]

## Checklist
- [ ] Code follows style guidelines
- [ ] Documentation updated
- [ ] Tests added/updated
- [ ] No breaking changes
```

## Code Standards

### PHP/Laravel

- Use PSR-12 code style
- Use type hints on all methods
- Use meaningful variable names
- Keep methods short and focused (< 50 lines)
- Use early returns to reduce nesting
- Add docblocks for complex logic

```php
// Good
public function getActiveProducts(int $limit = 12): Collection
{
    if ($limit < 1 || $limit > 100) {
        throw new InvalidArgumentException('Limit must be between 1 and 100');
    }

    return Product::active()
        ->orderBy('created_at', 'desc')
        ->limit($limit)
        ->get();
}

// Avoid
public function getProducts($limit = null)
{
    $products = [];
    $query = Product::query();
    if ($limit) {
        $query->limit($limit);
    }
    $query->where('status', 'active');
    $query->orderBy('created_at', 'desc');
    $products = $query->get();
    return $products;
}
```

### Blade Templates

- Use components for reusable parts
- Use meaningful slot names
- Avoid inline JavaScript
- Use consistent indentation (4 spaces)

```blade
{{-- Good component usage --}}
<x-product-card 
    :product="$product" 
    :featured="true" 
    @click="selectProduct"
/>

{{-- Avoid --}}
<div onclick="alert('test')">
  Test
</div>
```

### CSS/Tailwind

- Use Tailwind utilities
- Create custom components for repeated styles
- Use responsive prefixes
- Keep specificity low

```css
/* Good */
@layer components {
    .btn-primary {
        @apply px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition;
    }
}

/* Avoid */
.btn {
    padding: 8px 16px;
    background-color: #2563eb;
    color: white;
    border-radius: 4px;
}
.btn:hover {
    background-color: #1d4ed8;
}
```

### Testing

- Write clear test names
- Use arrange-act-assert pattern
- Test behavior, not implementation
- Aim for high coverage

```php
// Good test
public function test_product_price_must_be_positive()
{
    // Arrange
    $product = new Product();

    // Act & Assert
    $this->expectException(InvalidArgumentException::class);
    $product->setPrice(-100);
}

// Avoid
public function test_product()
{
    // Unclear what's being tested
    $p = Product::factory()->create();
    $this->assertTrue(true);
}
```

## Common Tasks

### Adding a New Feature

1. Create migration (if database changes)
   ```bash
   php artisan make:migration create_users_table
   ```

2. Create model (if new entity)
   ```bash
   php artisan make:model User -m
   ```

3. Create controller
   ```bash
   php artisan make:controller Api/UserController
   ```

4. Create tests
   ```bash
   php artisan make:test Feature/UserApiTest
   ```

5. Add routes
   ```php
   // routes/api.php
   Route::apiResource('users', UserController::class);
   ```

6. Write tests, then implementation

### Fixing a Bug

1. Write failing test
2. Identify root cause
3. Implement fix
4. Verify test passes
5. Check for related issues
6. Update documentation if needed

### Updating Dependencies

```bash
# Check outdated packages
composer outdated
npm outdated

# Update safely
composer update --dry-run
npm audit

# Update
composer update
npm update
```

## Getting Help

### Resources
- [Laravel Docs](https://laravel.com/docs)
- [Tailwind CSS Docs](https://tailwindcss.com/docs)
- [Vue.js Docs](https://vuejs.org/)

### Communication
- Create GitHub issues for bugs/features
- Use discussions for questions
- Join our development chat (if available)

## Code Review Process

1. **Automated Checks**
   - GitHub Actions tests must pass
   - Code coverage must not decrease
   - Linting checks must pass

2. **Manual Review**
   - At least 1 approval required
   - Reviewer checks logic, style, tests
   - Suggestions for improvements

3. **Merge**
   - Squash commits (optional)
   - Delete branch after merge
   - Close related issues

## Troubleshooting

### Tests failing locally but passing on CI?

```bash
# Clear cache
php artisan config:clear
php artisan cache:clear

# Recreate test database
php artisan migrate:fresh --seed

# Run tests again
php artisan test
```

### Git conflicts?

```bash
# View conflicts
git status

# Resolve conflicts in editor
# Then:
git add .
git rebase --continue
```

### Need to undo commits?

```bash
# Undo last commit (keep changes)
git reset --soft HEAD~1

# Undo last 3 commits
git reset --soft HEAD~3

# Force push (be careful!)
git push origin feature-branch --force-with-lease
```

## Performance Tips

- Use database indexes for queries
- Implement eager loading to avoid N+1 queries
- Cache expensive operations
- Use pagination for large datasets
- Minify assets in production
- Use CDN for static files

## Accessibility

- Use semantic HTML
- Include alt text for images
- Ensure proper color contrast
- Support keyboard navigation
- Test with screen readers

## Security

- Validate all user input
- Use CSRF tokens
- Escape output in templates
- Use prepared statements
- Keep dependencies updated
- Never commit secrets

---

**Thank you for contributing! 🎉**

We appreciate your efforts to improve this project. Happy coding!
