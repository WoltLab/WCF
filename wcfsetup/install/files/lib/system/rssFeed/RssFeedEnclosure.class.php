<?php

namespace wcf\system\rssFeed;

final class RssFeedEnclosure
{
    public function __construct(
        public readonly string $url,
        public readonly int $length,
        public readonly string $type
    ) {
    }
}
