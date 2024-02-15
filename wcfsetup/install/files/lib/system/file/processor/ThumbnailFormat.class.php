<?php

namespace wcf\system\file\processor;

final class ThumbnailFormat
{
    public function __construct(
        public readonly string $identifier,
        public readonly int $height,
        public readonly int $width,
        public readonly bool $retainDimensions,
    ) {
    }
}
