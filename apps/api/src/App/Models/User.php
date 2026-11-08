<?php declare(strict_types=1);

namespace Concept\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class User extends Model
{
    protected $table = 'users';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
    ];

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool
    {
        $hash = $this->getAttribute('password');

        return is_string($hash) && password_verify($password, $hash);
    }

    /** @return HasMany<ApiToken, $this> */
    public function tokens(): HasMany
    {
        return $this->hasMany(ApiToken::class);
    }
}
