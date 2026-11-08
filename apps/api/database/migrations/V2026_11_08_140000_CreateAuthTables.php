<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as CapsuleManager;

return new class extends Migration
{
    public function up(): void
    {
        CapsuleManager::schema()->create('users', function(Blueprint $table): void {
            $table->increments('id');
            $table->string('name', 120);
            $table->string('email', 190)->unique();
            $table->string('password', 255);
            $table->timestamps();
        });

        CapsuleManager::schema()->create('api_tokens', function(Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('name', 120)->nullable();
            $table->char('token_hash', 64)->unique();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        CapsuleManager::schema()->dropIfExists('api_tokens');
        CapsuleManager::schema()->dropIfExists('users');
    }
};
