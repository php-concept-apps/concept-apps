<?php declare(strict_types=1);

namespace Concept\App\Http\Requests\Admin;

use Concept\App\Dto\Tag\UpdateTagDto;
use Concept\Extensions\FormRequest\Requests\FormRequest;

/** @extends FormRequest<UpdateTagDto> */
final class UpdateTagRequest extends FormRequest
{
    protected ?string $dtoClass = UpdateTagDto::class;

    public function rules(): array
    {
        return [
            'name' => ['required', 'min:1', 'max:80'],
            'slug' => ['nullable', 'max:100'],
        ];
    }
}
