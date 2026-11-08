<?php declare(strict_types=1);

namespace Concept\App\Http\Response;

use Concept\Extensions\Http\Protocol\HttpStatusCode;

/**
 * BFF JSON envelope (experiment). Not part of Concept Http extension.
 */
final class JsonEnvelope
{
    public const string OK = 'ok';
    public const string PAYLOAD = 'payload';
    public const string ERROR = 'error';
    public const string MESSAGE = 'message';
    public const string CODE = 'code';
    public const string DETAILS = 'details';

    /**
     * @return array{ok: true, payload: mixed}
     */
    public static function success(mixed $payload = []): array
    {
        return [
            self::OK => true,
            self::PAYLOAD => $payload,
        ];
    }

    /**
     * @param array<string, mixed> $details
     * @return array{ok: false, error: array{message: string, code: int, details?: array<string, mixed>}}
     */
    public static function error(
        string $message,
        int $code = HttpStatusCode::INTERNAL_SERVER_ERROR,
        array $details = [],
    ): array {
        $error = [
            self::MESSAGE => $message,
            self::CODE => $code,
        ];

        if ($details !== []) {
            $error[self::DETAILS] = $details;
        }

        return [
            self::OK => false,
            self::ERROR => $error,
        ];
    }
}
