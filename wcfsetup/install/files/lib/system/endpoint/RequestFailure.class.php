<?php

namespace wcf\system\endpoint;

enum RequestFailure
{
    case InternalError;
    case MethodNotAllowed;
    case PermissionDenied;
    case ValidationFailed;
    case UnknownEndpoint;

    public function toString(): string
    {
        return match ($this) {
            self::InternalError => 'api_error',
            self::MethodNotAllowed => 'invalid_request_error',
            self::PermissionDenied => 'invalid_request_error',
            self::ValidationFailed => 'invalid_request_error',
            self::UnknownEndpoint => 'invalid_request_error',
        };
    }

    public function toStatusCode(): int
    {
        return match ($this) {
            self::InternalError => 503,
            self::MethodNotAllowed => 405,
            self::PermissionDenied => 403,
            self::ValidationFailed => 400,
            self::UnknownEndpoint => 404,
        };
    }
}
