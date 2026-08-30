<?php

namespace wcf\data\comment\response;

use wcf\data\comment\Comment;
use wcf\data\DatabaseObjectCollection;
use wcf\data\TCollectionEmbeddedObjects;
use wcf\data\TCollectionReactions;
use wcf\data\TCollectionUserProfiles;
use wcf\system\cache\runtime\CommentRuntimeCache;

/**
 * Represents a collection of comment responses.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectCollection<CommentResponse>
 */
class CommentResponseCollection extends DatabaseObjectCollection
{
    use TCollectionUserProfiles;
    use TCollectionEmbeddedObjects;
    use TCollectionReactions;

    private bool $commentsCached = false;

    public function getComment(CommentResponse $object): ?Comment
    {
        $this->cacheComments();

        return CommentRuntimeCache::getInstance()->getObject($object->commentID);
    }

    private function cacheComments(): void
    {
        if ($this->commentsCached) {
            return;
        }

        $this->commentsCached = true;

        $commentIDs = \array_unique(\array_map(
            static fn($object) => $object->commentID,
            $this->getObjects()
        ));

        CommentRuntimeCache::getInstance()->cacheObjectIDs($commentIDs);
    }

    #[\Override]
    protected function getReactionObjectType(): string
    {
        return 'com.woltlab.wcf.comment.response';
    }
}
