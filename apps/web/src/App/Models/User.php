<?php declare(strict_types=1);

namespace Concept\App\Models;

use Concept\App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class User extends Model
{
    use SoftDeletes;

    public const string TABLE_NAME = 'users';

    public const string FIELD_ID = 'id';
    public const string FIELD_NAME = 'name';
    public const string FIELD_EMAIL = 'email';
    public const string FIELD_PASSWORD = 'password';
    public const string FIELD_STATUS = 'status';
    public const string FIELD_REMEMBER_TOKEN = 'remember_token';
    public const string FIELD_CREATED_AT = 'created_at';
    public const string FIELD_UPDATED_AT = 'updated_at';
    public const string FIELD_DELETED_AT = 'deleted_at';

    protected $table = self::TABLE_NAME;

    /** @var list<string> */
    protected $fillable = [
        self::FIELD_NAME,
        self::FIELD_EMAIL,
        self::FIELD_PASSWORD,
        self::FIELD_STATUS,
        self::FIELD_REMEMBER_TOKEN,
    ];

    /** @var list<string> */
    protected $hidden = [
        self::FIELD_PASSWORD,
        self::FIELD_REMEMBER_TOKEN,
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            self::FIELD_STATUS => UserStatus::class,
        ];
    }

    public function getId(): int
    {
        $id = $this->getAttribute(self::FIELD_ID);

        return is_numeric($id) ? (int) $id : 0;
    }

    public function getName(): string
    {
        $name = $this->getAttribute(self::FIELD_NAME);

        return is_string($name) ? $name : '';
    }

    public function getEmail(): string
    {
        $email = $this->getAttribute(self::FIELD_EMAIL);

        return is_string($email) ? $email : '';
    }

    public function getStatus(): ?UserStatus
    {
        $status = $this->getAttribute(self::FIELD_STATUS);

        return $status instanceof UserStatus ? $status : null;
    }

    public function getRememberToken(): ?string
    {
        $token = $this->getAttribute(self::FIELD_REMEMBER_TOKEN);

        return is_string($token) ? $token : null;
    }

    public function setRememberToken(?string $token): void
    {
        $this->setAttribute(self::FIELD_REMEMBER_TOKEN, $token);
    }

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes[self::FIELD_PASSWORD] = password_hash($value, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool
    {
        $hash = $this->getAttribute(self::FIELD_PASSWORD);

        return is_string($hash) && password_verify($password, $hash);
    }

    public function isActive(): bool
    {
        return $this->getStatus() === UserStatus::Active;
    }

    /** @return HasMany<Post, $this> */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, Post::FIELD_USER_ID, self::FIELD_ID);
    }
}
