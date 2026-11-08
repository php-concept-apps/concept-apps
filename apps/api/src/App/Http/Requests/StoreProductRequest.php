<?php declare(strict_types=1);

namespace Concept\App\Http\Requests;

use Concept\App\Enums\ProductStatus;
use Concept\Extensions\FormRequest\Requests\FormRequest;

/**
 * @extends FormRequest<never>
 */
final class StoreProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category_id' => 'required|integer|min:1',
            'sku' => 'required|min:1|max:64',
            'name' => 'required|min:1|max:180',
            'slug' => 'required|min:1|max:200',
            'description' => 'nullable|max:10000',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|min:3|max:3',
            'stock' => 'required|integer|min:0',
            'status' => 'required|in:' . implode(',', ProductStatus::values()),
            'weight_grams' => 'nullable|integer|min:0',
            'attributes' => 'nullable|array',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|min:1',
            'published_at' => 'nullable',
        ];
    }
}
