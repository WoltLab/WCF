<?php

namespace wcf\system\endpoint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class PostRequest extends RequestType
{
    public function __construct(string $uri)
    {
        parent::__construct(RequestMethod::POST, $uri);
    }
}
