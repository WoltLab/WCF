<?php

namespace wcf\system\endpoint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class RequestType
{
    public function __construct(
        public readonly RequestMethod $method,
        public readonly string $uri,
    ) {
    }
}
