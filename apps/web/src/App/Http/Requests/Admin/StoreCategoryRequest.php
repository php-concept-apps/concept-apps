<?php declare(strict_types=1);

namespace Concept\App\Http\Requests\Admin;

use Concept\App\Dto\Category\StoreCategoryDto;
use Concept\Extensions\FormRequest\Requests\FormRequest;

/** @extends FormRequest<StoreCategoryDto> */
final class StoreCategoryRequest extends FormRequest
{
    protected ?string $dtoClass = StoreCategoryDto::class;

    public function rules(): array
    {
        return [
            'name' => ['required', 'min:2', 'max:120'],
            'slug' => ['nullable', 'max:160'],
            'description' => ['nullable', 'max:500'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }
}
