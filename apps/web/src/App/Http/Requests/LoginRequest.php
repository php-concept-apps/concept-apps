<?php declare(strict_types=1);

namespace Concept\App\Http\Requests;

use Concept\App\Dto\LoginDto;
use Concept\App\Models\User;
use Concept\Extensions\FormRequest\Requests\FormRequest;

/** @extends FormRequest<LoginDto> */
final class LoginRequest extends FormRequest
{
    protected ?string $dtoClass = LoginDto::class;

    public function rules(): array
    {
        return [
            User::FIELD_EMAIL => ['required', 'email'],
            User::FIELD_PASSWORD => ['required'],
        ];
    }
}
