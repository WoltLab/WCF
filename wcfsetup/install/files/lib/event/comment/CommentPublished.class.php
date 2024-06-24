<?php

namespace wcf\event\comment;

use wcf\data\comment\Comment;
use wcf\event\IPsr14Event;

/**
 * Indicates that a new comment has been published. This can happen directly when a comment is created
 * or be delayed if a comment has first been checked and approved by a moderator.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class CommentPublished implements IPsr14Event
{
    public function __construct(
        public readonly Comment $comment,
    ) {
    }
}
