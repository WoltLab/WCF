<?php

namespace wcf\system\endpoint;

abstract class RequestType
{
    public function __construct(
        public readonly RequestMethod $method,
        public readonly string $uri,
    ) {
    }
}
