<?php declare(strict_types=1);

use Concept\App\Enums\ProductStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as CapsuleManager;

return new class extends Migration
{
    public function up(): void
    {
        CapsuleManager::schema()->create('categories', function(Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('parent_id')->nullable();
            $table->string('name', 120);
            $table->string('slug', 160)->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('categories')->nullOnDelete();
            $table->index('parent_id');
        });

        CapsuleManager::schema()->create('tags', function(Blueprint $table): void {
            $table->increments('id');
            $table->string('name', 80);
            $table->string('slug', 100)->unique();
            $table->timestamps();
        });

        CapsuleManager::schema()->create('products', function(Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('category_id');
            $table->string('sku', 64)->unique();
            $table->string('name', 180);
            $table->string('slug', 200)->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->char('currency', 3)->default('USD');
            $table->unsignedInteger('stock')->default(0);
            $table->string('status', 16)->default(ProductStatus::Draft->value);
            $table->unsignedInteger('weight_grams')->nullable();
            $table->json('attributes')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
            $table->index(['category_id', 'status']);
            $table->index('published_at');
        });

        CapsuleManager::schema()->create('product_tag', function(Blueprint $table): void {
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('tag_id');
            $table->primary(['product_id', 'tag_id']);

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        CapsuleManager::schema()->dropIfExists('product_tag');
        CapsuleManager::schema()->dropIfExists('products');
        CapsuleManager::schema()->dropIfExists('tags');
        CapsuleManager::schema()->dropIfExists('categories');
    }
};
