<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('image')->nullable();
            $table->text('details')->nullable();
            $table->integer('stock')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active');
            $table->integer('view_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('category_id');
            $table->index('slug');
            $table->index('status');
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
