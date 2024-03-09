<?php

namespace wcf\system\endpoint\exception;

use wcf\system\endpoint\error\RouteParameterError;

final class RouteParameterMismatch extends \Exception
{
    public function __construct(
        public readonly RouteParameterError $type,
        public readonly string $name,
    ) {
        parent::__construct(
            \sprintf('The parameter "%s" is malformed', $name),
        );
    }
}
