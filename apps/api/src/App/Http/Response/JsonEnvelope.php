<?php declare(strict_types=1);

namespace Concept\App\Http\Response;

use Concept\Extensions\Http\Protocol\HttpStatusCode;

/**
 * API JSON envelope. Not part of Concept Http extension.
 */
final class JsonEnvelope
{
    public const string OK = 'ok';
    public const string PAYLOAD = 'payload';
    public const string ERROR = 'error';
    public const string MESSAGE = 'message';
    public const string CODE = 'code';
    public const string DETAILS = 'details';

    public const string ITEMS = 'items';
    public const string META = 'meta';
    public const string LINKS = 'links';
    public const string META_CURRENT_PAGE = 'current_page';
    public const string META_PER_PAGE = 'per_page';
    public const string META_TOTAL = 'total';
    public const string META_LAST_PAGE = 'last_page';
    public const string META_FROM = 'from';
    public const string META_TO = 'to';
    public const string LINK_FIRST = 'first';
    public const string LINK_LAST = 'last';
    public const string LINK_PREV = 'prev';
    public const string LINK_NEXT = 'next';

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
