<?php

namespace wcf\system\endpoint;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class PostRequest
{
    public function __construct(
        public readonly string $uri,
    ) {
    }
}
