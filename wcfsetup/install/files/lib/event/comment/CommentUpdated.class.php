<?php

namespace wcf\event\comment;

use wcf\data\comment\Comment;
use wcf\event\IPsr14Event;

/**
 * Indicates that a comment has been updated.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class CommentUpdated implements IPsr14Event
{
    public function __construct(
        public readonly Comment $comment,
    ) {
    }
}
