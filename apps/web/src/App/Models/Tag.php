<?php declare(strict_types=1);

namespace Concept\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Tag extends Model
{
    public const string TABLE_NAME = 'tags';

    public const string FIELD_ID = 'id';
    public const string FIELD_NAME = 'name';
    public const string FIELD_SLUG = 'slug';
    public const string FIELD_CREATED_AT = 'created_at';
    public const string FIELD_UPDATED_AT = 'updated_at';

    public const string RELATION_POSTS = 'posts';

    protected $table = self::TABLE_NAME;

    /** @var list<string> */
    protected $fillable = [
        self::FIELD_NAME,
        self::FIELD_SLUG,
    ];

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

    public function getSlug(): string
    {
        $slug = $this->getAttribute(self::FIELD_SLUG);

        return is_string($slug) ? $slug : '';
    }

    /** @return BelongsToMany<Post, $this> */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_tag');
    }
}

