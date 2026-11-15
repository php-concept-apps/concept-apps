<?php declare(strict_types=1);

namespace Concept\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Category extends Model
{
    public const string TABLE_NAME = 'categories';

    public const string FIELD_ID = 'id';
    public const string FIELD_NAME = 'name';
    public const string FIELD_SLUG = 'slug';
    public const string FIELD_DESCRIPTION = 'description';
    public const string FIELD_SORT_ORDER = 'sort_order';
    public const string FIELD_CREATED_AT = 'created_at';
    public const string FIELD_UPDATED_AT = 'updated_at';

    public const string RELATION_POSTS = 'posts';

    protected $table = self::TABLE_NAME;

    /** @var list<string> */
    protected $fillable = [
        self::FIELD_NAME,
        self::FIELD_SLUG,
        self::FIELD_DESCRIPTION,
        self::FIELD_SORT_ORDER,
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

    public function getDescription(): ?string
    {
        $description = $this->getAttribute(self::FIELD_DESCRIPTION);

        return is_string($description) ? $description : null;
    }

    public function getSortOrder(): int
    {
        $sortOrder = $this->getAttribute(self::FIELD_SORT_ORDER);

        return is_numeric($sortOrder) ? (int) $sortOrder : 0;
    }

    /** @return HasMany<Post, $this> */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, Post::FIELD_CATEGORY_ID, self::FIELD_ID);
    }
}
