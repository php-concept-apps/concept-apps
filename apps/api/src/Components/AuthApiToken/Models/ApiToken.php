<?php declare(strict_types=1);

namespace Concept\Components\AuthApiToken\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ApiToken extends Model
{
    protected $table = 'api_tokens';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'name',
        'token_hash',
        'last_used_at',
        'expires_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        $expiresAt = $this->getAttribute('expires_at');
        if (!$expiresAt instanceof DateTimeInterface) {
            return false;
        }

        return $expiresAt->getTimestamp() < time();
    }
}
