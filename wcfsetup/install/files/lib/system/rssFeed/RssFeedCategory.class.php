<?php

namespace wcf\system\rssFeed;

final class RssFeedCategory
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $domain = null,
    ) {
    }
}
