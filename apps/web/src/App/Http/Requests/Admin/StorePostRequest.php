<?php declare(strict_types=1);

namespace Concept\App\Http\Requests\Admin;

use Concept\App\Dto\Post\StorePostDto;
use Concept\App\Enums\PostStatus;
use Concept\App\Models\Post;
use Concept\Extensions\FormRequest\Requests\FormRequest;

/** @extends FormRequest<StorePostDto> */
final class StorePostRequest extends FormRequest
{
    protected ?string $dtoClass = StorePostDto::class;

    public function rules(): array
    {
        $statuses = implode(',', array_column(PostStatus::cases(), 'value'));

        return [
            Post::FIELD_TITLE => ['required', 'min:3', 'max:200'],
            Post::FIELD_SLUG => ['nullable', 'max:220'],
            Post::FIELD_EXCERPT => ['nullable', 'max:500'],
            Post::FIELD_BODY => ['required', 'min:1'],
            Post::FIELD_CATEGORY_ID => ['nullable', 'integer'],
            Post::FIELD_STATUS => ['required', 'in:' . $statuses],
            Post::FIELD_PUBLISHED_AT => ['nullable'],
            Post::INPUT_TAG_IDS => ['nullable', 'array'],
            Post::INPUT_TAG_IDS . '.*' => ['integer'],
        ];
    }
}
