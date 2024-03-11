<?php

namespace wcf\system\endpoint;

enum RequestMethod
{
    case GET;
    case POST;

    public function toString(): string
    {
        return match ($this) {
            self::GET => 'GET',
            self::POST => 'POST',
        };
    }
}
