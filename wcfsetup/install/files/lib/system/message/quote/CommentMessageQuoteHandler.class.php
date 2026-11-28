<?php

namespace wcf\system\message\quote;

use wcf\data\comment\Comment;
use wcf\data\IMessage;

/**
 * IMessageQuoteHandler implementation for comments.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CommentMessageQuoteHandler extends AbstractMessageQuoteHandler
{
    #[\Override]
    public function getMessage(int $objectID): ?IMessage
    {
        $comment = new Comment($objectID);
        if ($comment->isNil()) {
            return null;
        }

        $commentManager = $comment->getCommentManager();
        if (!$commentManager->isAccessible($comment->objectID)) {
            return null;
        }

        if ($comment->isDisabled !== 0 && !$commentManager->canModerate($comment->objectTypeID, $comment->objectID)) {
            return null;
        }

        return $comment;
    }
}
