<?php declare(strict_types=1);

namespace Concept\App\Http\Requests\Admin;

use Concept\App\Dto\Category\StoreCategoryDto;
use Concept\App\Models\Category;
use Concept\Extensions\FormRequest\Requests\FormRequest;

/** @extends FormRequest<StoreCategoryDto> */
final class StoreCategoryRequest extends FormRequest
{
    protected ?string $dtoClass = StoreCategoryDto::class;

    public function rules(): array
    {
        return [
            Category::FIELD_NAME => ['required', 'min:2', 'max:120'],
            Category::FIELD_SLUG => ['nullable', 'max:160'],
            Category::FIELD_DESCRIPTION => ['nullable', 'max:500'],
            Category::FIELD_SORT_ORDER => ['nullable', 'integer'],
        ];
    }
}
