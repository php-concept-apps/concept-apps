<?php declare(strict_types=1);

namespace Concept\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Product extends Model
{
    public const string STATUS_DRAFT = 'draft';
    public const string STATUS_ACTIVE = 'active';
    public const string STATUS_ARCHIVED = 'archived';

    protected $table = 'products';

    /** @var list<string> */
    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'stock',
        'status',
        'weight_grams',
        'attributes',
        'published_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'attributes' => 'array',
            'published_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }
}
