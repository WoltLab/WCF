<?php

namespace wcf\system\message\quote;

use wcf\data\comment\response\CommentResponse;
use wcf\data\IMessage;

/**
 * IMessageQuoteHandler implementation for comment responses.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CommentResponseMessageQuoteHandler extends AbstractMessageQuoteHandler
{
    #[\Override]
    public function getMessage(int $objectID): ?IMessage
    {
        $response = new CommentResponse($objectID);
        if ($response->isNil()) {
            return null;
        }

        $comment = $response->getComment();
        $commentManager = $comment->getCommentManager();
        if (!$commentManager->isAccessible($comment->objectID)) {
            return null;
        }

        if (
            ($comment->isDisabled !== 0 || $response->isDisabled !== 0)
            && !$commentManager->canModerate($comment->objectTypeID, $comment->objectID)
        ) {
            return null;
        }

        return $response;
    }
}
