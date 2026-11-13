<?php declare(strict_types=1);

namespace Concept\App\Http\Requests;

use Concept\App\Dto\LoginDto;
use Concept\Extensions\FormRequest\Requests\FormRequest;

/** @extends FormRequest<LoginDto> */
final class LoginRequest extends FormRequest
{
    protected ?string $dtoClass = LoginDto::class;

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }
}
