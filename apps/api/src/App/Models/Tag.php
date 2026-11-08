<?php declare(strict_types=1);

namespace Concept\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Tag extends Model
{
    protected $table = 'tags';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
    ];

    /** @return BelongsToMany<Product, $this> */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_tag');
    }
}
