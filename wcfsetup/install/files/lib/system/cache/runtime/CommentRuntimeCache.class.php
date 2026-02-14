<?php

namespace wcf\system\cache\runtime;

use wcf\data\comment\Comment;
use wcf\data\comment\CommentList;

/**
 * Runtime cache implementation for comments.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @extends AbstractRuntimeCache<Comment, CommentList>
 */
class CommentRuntimeCache extends AbstractRuntimeCache
{
    /**
     * @inheritDoc
     */
    protected $listClassName = CommentList::class;

    /**
     * @since 6.2
     */
    public function cacheComment(Comment $comment): void
    {
        $this->objects[$comment->commentID] = $comment;
    }
}
