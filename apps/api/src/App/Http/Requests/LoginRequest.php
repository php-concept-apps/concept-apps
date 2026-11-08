<?php declare(strict_types=1);

namespace Concept\App\Http\Requests;

use Concept\Extensions\FormRequest\Requests\FormRequest;

/**
 * @extends FormRequest<never>
 */
final class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|min:1',
            'token_name' => 'nullable|max:120',
        ];
    }
}
