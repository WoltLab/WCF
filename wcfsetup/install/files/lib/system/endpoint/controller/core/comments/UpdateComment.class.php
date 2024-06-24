<?php

namespace wcf\system\endpoint\controller\core\comments;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\comment\Comment;
use wcf\event\message\MessageSpamChecking;
use wcf\http\Helper;
use wcf\system\comment\CommentHandler;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\event\EventHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;
use wcf\util\UserUtil;

/**
 * API endpoint for the update of comments.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
#[PostRequest('/core/comments/{id:\d+}')]
final class UpdateComment implements IController
{
    use TCommentMessageValidator;

    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $comment = Helper::fetchObjectFromRequestParameter($variables['id'], Comment::class);

        $this->assertCommentIsEditable($comment);

        $parameters = Helper::mapApiParameters($request, UpdateCommentParameters::class);

        $htmlInputProcessor = $this->validateMessage($parameters->message, false, $comment->commentID);

        $event = new MessageSpamChecking(
            $htmlInputProcessor,
            WCF::getUser()->userID ? WCF::getUser() : null,
            UserUtil::getIpAddress(),
        );
        EventHandler::getInstance()->fire($event);
        if ($event->defaultPrevented()) {
            throw new PermissionDeniedException();
        }

        (new \wcf\system\comment\command\UpdateComment(
            $comment,
            $htmlInputProcessor,
        ))();

        return new JsonResponse([]);
    }

    private function assertCommentIsEditable(Comment $comment): void
    {
        $commentManager = CommentHandler::getInstance()->getCommentManagerByID($comment->objectTypeID);
        if (!$commentManager->canEditComment($comment)) {
            throw new PermissionDeniedException();
        }
    }
}

/** @internal */
final class UpdateCommentParameters
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $message,
    ) {
    }
}
