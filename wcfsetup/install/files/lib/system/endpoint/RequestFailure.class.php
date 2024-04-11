<?php

namespace wcf\system\endpoint;

/**
 * Represents different classes of errors for API endpoints.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
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
