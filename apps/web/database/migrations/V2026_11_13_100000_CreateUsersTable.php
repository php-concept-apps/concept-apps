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
            $table->string('status', 20)->default('active');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        CapsuleManager::schema()->dropIfExists('users');
    }
};
