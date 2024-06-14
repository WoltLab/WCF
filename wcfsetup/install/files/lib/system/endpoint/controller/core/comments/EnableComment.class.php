<?php

namespace wcf\system\endpoint\controller\core\comments;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\comment\Comment;
use wcf\http\Helper;
use wcf\system\comment\CommentHandler;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;

/**
 * API endpoint for enabling comments.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
#[PostRequest('/core/comments/{id:\d+}/enable')]
final class EnableComment implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $comment = Helper::fetchObjectFromRequestParameter($variables['id'], Comment::class);

        $this->assertCommentCanBeEnabled($comment);

        if (!$comment->isDisabled) {
            (new \wcf\system\comment\command\PublishComment($comment))();
        }

        return new JsonResponse([]);
    }

    private function assertCommentCanBeEnabled(Comment $comment): void
    {
        $commentManager = CommentHandler::getInstance()->getCommentManagerByID($comment->objectTypeID);
        if (!$commentManager->canModerate($comment->objectTypeID, $comment->objectID)) {
            throw new PermissionDeniedException();
        }
    }
}
