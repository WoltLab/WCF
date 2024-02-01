<?php

namespace wcf\system\rssFeed;

final class RssFeedSource
{
    public function __construct(
        public readonly string $name,
        public readonly string $url,
    ) {
    }
}
