<?php

namespace wcf\system\endpoint;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class Parameters
{
    public function __construct(
        public readonly string $arrayShape = '',
    ) {
    }
}
