<?php declare(strict_types=1);

namespace Concept\App\Database\Seeders;

use Concept\App\Enums\UserStatus;
use Concept\Extensions\DatabaseEloquent\Contracts\SeederInterface;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Illuminate\Database\Seeder;

final class UserSeeder extends Seeder implements SeederInterface
{
    public function run(): void
    {
        $connection = CapsuleManager::schema()->getConnection();
        $connection->statement('SET FOREIGN_KEY_CHECKS=0');
        $connection->table('users')->truncate();
        $connection->statement('SET FOREIGN_KEY_CHECKS=1');

        $now = date('Y-m-d H:i:s');

        $connection->table('users')->insert([
            [
                'name' => 'Ada',
                'email' => 'ada@example.com',
                'password' => password_hash('secret', PASSWORD_DEFAULT),
                'status' => UserStatus::Active->value,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
