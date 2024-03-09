<?php

namespace wcf\system\endpoint\exception;

use wcf\system\endpoint\error\ControllerError;

class ControllerMalformed extends \Exception
{
    public function __construct(
        public readonly ControllerError $type,
        public readonly \ReflectionParameter $parameter,
    ) {

        parent::__construct(
            \sprintf(
                'The parameter "%s" for %s::%s could not be processed.',
                $parameter->name,
                $parameter->getDeclaringClass()->name,
                $parameter->getDeclaringFunction()->name,
            ),
        );
    }
}
