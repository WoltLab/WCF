<?php

namespace wcf\system\endpoint;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class GetRequest extends RequestType
{
    public function __construct(string $uri)
    {
        parent::__construct(RequestMethod::GET, $uri);
    }
}
