<?php

namespace wcf\system\endpoint;

enum RequestMethod
{
    case DELETE;
    case GET;
    case POST;

    public function toString(): string
    {
        return match ($this) {
            self::DELETE => 'DELETE',
            self::GET => 'GET',
            self::POST => 'POST',
        };
    }
}
