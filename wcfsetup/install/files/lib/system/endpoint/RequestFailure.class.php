<?php

namespace wcf\system\endpoint;

enum RequestFailure
{
    case INTERNAL_ERROR;
    case METHOD_NOT_ALLOWED;
    case PERMISSION_DENIED;
    case VALIDATION_FAILED;
    case UNKNOWN_ENDPOINT;

    public function toString(): string
    {
        return match ($this) {
            self::INTERNAL_ERROR => 'api_error',
            self::METHOD_NOT_ALLOWED => 'invalid_request_error',
            self::PERMISSION_DENIED => 'invalid_request_error',
            self::VALIDATION_FAILED => 'invalid_request_error',
            self::UNKNOWN_ENDPOINT => 'invalid_request_error',
        };
    }

    public function toStatusCode(): int
    {
        return match ($this) {
            self::INTERNAL_ERROR => 503,
            self::METHOD_NOT_ALLOWED => 405,
            self::PERMISSION_DENIED => 403,
            self::VALIDATION_FAILED => 400,
            self::UNKNOWN_ENDPOINT => 404,
        };
    }
}
