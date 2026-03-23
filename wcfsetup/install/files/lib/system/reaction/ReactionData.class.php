<?php

namespace wcf\system\reaction;

final class ReactionData
{
    public function __construct(
        public readonly string $objectType,
        public readonly int $objectID,
        public readonly int $reactionTypeID = 0,
        public readonly ?string $cachedReactions = null,
        public readonly ?string $reactionsJson = null,
    ) {}
}
