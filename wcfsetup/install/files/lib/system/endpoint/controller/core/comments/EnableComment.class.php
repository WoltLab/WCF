<?php

namespace wcf\system\endpoint\controller\core\comments;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\comment\Comment;
use wcf\http\Helper;
use wcf\system\comment\CommentHandler;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;
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

        (new \wcf\system\comment\command\PublishComment($comment))();

        return new JsonResponse([]);
    }

    private function assertCommentCanBeEnabled(Comment $comment): void
    {
        if (!$comment->isDisabled) {
            throw new IllegalLinkException();
        }

        $processor = CommentHandler::getInstance()->getObjectType($comment->objectTypeID)->getProcessor();
        \assert($processor instanceof ICommentManager);
        if (!$processor->canModerate($comment->objectTypeID, $comment->objectID)) {
            throw new PermissionDeniedException();
        }
    }
}
