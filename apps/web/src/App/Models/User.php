<?php declare(strict_types=1);

namespace Concept\App\Models;

use Concept\App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class User extends Model
{
    use SoftDeletes;

    protected $table = 'users';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'remember_token',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => UserStatus::class,
        ];
    }

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool
    {
        $hash = $this->getAttribute('password');

        return is_string($hash) && password_verify($password, $hash);
    }

    public function isActive(): bool
    {
        return $this->getAttribute('status') === UserStatus::Active;
    }
}
