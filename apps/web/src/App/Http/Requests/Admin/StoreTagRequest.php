<?php declare(strict_types=1);

namespace Concept\App\Http\Requests\Admin;

use Concept\App\Dto\Tag\StoreTagDto;
use Concept\Extensions\FormRequest\Requests\FormRequest;

/** @extends FormRequest<StoreTagDto> */
final class StoreTagRequest extends FormRequest
{
    protected ?string $dtoClass = StoreTagDto::class;

    public function rules(): array
    {
        return [
            'name' => ['required', 'min:1', 'max:80'],
            'slug' => ['nullable', 'max:100'],
        ];
    }
}
