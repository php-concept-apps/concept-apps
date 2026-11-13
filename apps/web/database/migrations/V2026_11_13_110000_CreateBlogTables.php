<?php declare(strict_types=1);

use Concept\App\Enums\PostStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as CapsuleManager;

return new class extends Migration
{
    public function up(): void
    {
        CapsuleManager::schema()->create('categories', function(Blueprint $table): void {
            $table->increments('id');
            $table->string('name', 120);
            $table->string('slug', 160)->unique();
            $table->string('description', 500)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        CapsuleManager::schema()->create('tags', function(Blueprint $table): void {
            $table->increments('id');
            $table->string('name', 80);
            $table->string('slug', 100)->unique();
            $table->timestamps();
        });

        CapsuleManager::schema()->create('posts', function(Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('category_id')->nullable();
            $table->string('title', 200);
            $table->string('slug', 220)->unique();
            $table->string('excerpt', 500)->nullable();
            $table->text('body');
            $table->string('cover_image', 255)->nullable();
            $table->string('status', 20)->default(PostStatus::Draft->value);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->index(['status', 'published_at']);
            $table->index('category_id');
            $table->index('user_id');
        });

        CapsuleManager::schema()->create('post_tag', function(Blueprint $table): void {
            $table->unsignedInteger('post_id');
            $table->unsignedInteger('tag_id');
            $table->primary(['post_id', 'tag_id']);

            $table->foreign('post_id')->references('id')->on('posts')->cascadeOnDelete();
            $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        CapsuleManager::schema()->dropIfExists('post_tag');
        CapsuleManager::schema()->dropIfExists('posts');
        CapsuleManager::schema()->dropIfExists('tags');
        CapsuleManager::schema()->dropIfExists('categories');
    }
};
