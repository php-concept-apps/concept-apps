<?php declare(strict_types=1);

namespace Concept\App\Http\Requests\Admin;

use Concept\App\Dto\Post\StorePostDto;
use Concept\App\Enums\PostStatus;
use Concept\Extensions\FormRequest\Requests\FormRequest;

/** @extends FormRequest<StorePostDto> */
final class StorePostRequest extends FormRequest
{
    protected ?string $dtoClass = StorePostDto::class;

    public function rules(): array
    {
        $statuses = implode(',', array_column(PostStatus::cases(), 'value'));

        return [
            'title' => ['required', 'min:3', 'max:200'],
            'slug' => ['nullable', 'max:220'],
            'excerpt' => ['nullable', 'max:500'],
            'body' => ['required', 'min:1'],
            'category_id' => ['nullable', 'integer'],
            'status' => ['required', 'in:' . $statuses],
            'published_at' => ['nullable'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer'],
        ];
    }
}
