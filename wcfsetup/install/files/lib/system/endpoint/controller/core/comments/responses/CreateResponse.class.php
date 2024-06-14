<?php

namespace wcf\system\endpoint\controller\core\comments\responses;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\comment\Comment;
use wcf\event\message\MessageSpamChecking;
use wcf\http\Helper;
use wcf\system\comment\CommentHandler;
use wcf\system\endpoint\controller\core\comments\TCommentMessageValidator;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\event\EventHandler;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\flood\FloodControl;
use wcf\system\WCF;
use wcf\util\UserUtil;

/**
 * API endpoint for the creation of new responses.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
#[PostRequest('/core/comments/responses')]
final class CreateResponse implements IController
{
    use TCommentMessageValidator;

    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        try {
            CommentHandler::enforceFloodControl();
        } catch (NamedUserException $e) {
            throw new UserInputException('message', $e->getMessage());
        }

        $parameters = Helper::mapApiParameters($request, CreateResponseParameters::class);
        $comment = Helper::fetchObjectFromRequestParameter($parameters->commentID, Comment::class);
        $commentManager = CommentHandler::getInstance()->getCommentManagerByID($comment->objectTypeID);

        $this->assertResponseIsPossible($comment);

        $username = '';
        if (!WCF::getUser()->userID) {
            $username = UserUtil::verifyGuestToken($parameters->guestToken);
            if ($username === null) {
                throw new UserInputException('guestToken');
            }
        }

        $isDisabled = !$commentManager->canAddWithoutApproval($comment->objectID);

        $htmlInputProcessor = $this->validateMessage($parameters->message, true);

        $event = new MessageSpamChecking(
            $htmlInputProcessor,
            WCF::getUser()->userID ? WCF::getUser() : null,
            UserUtil::getIpAddress(),
        );
        EventHandler::getInstance()->fire($event);
        if ($event->defaultPrevented()) {
            $isDisabled = true;
        }

        $response = (new \wcf\system\comment\response\command\CreateResponse(
            $comment,
            $htmlInputProcessor,
            WCF::getUser()->userID ? WCF::getUser() : null,
            $username,
            $isDisabled,
        ))();

        FloodControl::getInstance()->registerContent('com.woltlab.wcf.comment');

        return new JsonResponse([
            'responseID' => $response->responseID,
        ]);
    }

    private function assertResponseIsPossible(Comment $comment): void
    {
        $commentManager = CommentHandler::getInstance()->getCommentManagerByID($comment->objectTypeID);
        if (!$commentManager->canAdd($comment->objectID)) {
            throw new PermissionDeniedException();
        }

        if ($comment->isDisabled && !$commentManager->canModerate($comment->objectTypeID, $comment->objectID)) {
            throw new PermissionDeniedException();
        }
    }
}

/** @internal */
final class CreateResponseParameters
{
    public function __construct(
        /** @var positive-int **/
        public readonly int $commentID,

        /** @var non-empty-string */
        public readonly string $message,

        public readonly string $guestToken,
    ) {
    }
}
