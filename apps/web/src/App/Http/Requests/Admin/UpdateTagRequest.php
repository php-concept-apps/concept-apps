<?php declare(strict_types=1);

namespace Concept\App\Http\Requests\Admin;

use Concept\App\Dto\Tag\UpdateTagDto;
use Concept\App\Models\Tag;
use Concept\Extensions\FormRequest\Requests\FormRequest;

/** @extends FormRequest<UpdateTagDto> */
final class UpdateTagRequest extends FormRequest
{
    protected ?string $dtoClass = UpdateTagDto::class;

    public function rules(): array
    {
        return [
            Tag::FIELD_NAME => ['required', 'min:1', 'max:80'],
            Tag::FIELD_SLUG => ['nullable', 'max:100'],
        ];
    }
}
