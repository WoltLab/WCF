<?php

namespace wcf\system\endpoint\controller\core\comments;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\event\message\MessageSpamChecking;
use wcf\http\Helper;
use wcf\system\comment\CommentHandler;
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
 * API endpoint for the creation of new comments.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
#[PostRequest('/core/comments')]
final class CreateComment implements IController
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

        $parameters = Helper::mapApiParameters($request, CreateCommentParameters::class);
        $objectType = CommentHandler::getInstance()->getObjectType($parameters->objectTypeID);
        if ($objectType === null) {
            throw new UserInputException('objectTypeID');
        }

        if (!$objectType->getProcessor()->canAdd($parameters->objectID)) {
            throw new PermissionDeniedException();
        }

        $username = '';
        if (!WCF::getUser()->userID) {
            $username = UserUtil::verifyGuestToken($parameters->guestToken);
            if ($username === null) {
                throw new UserInputException('guestToken');
            }
        }

        $isDisabled = !$objectType->getProcessor()->canAddWithoutApproval($parameters->objectID);

        $htmlInputProcessor = $this->validateMessage($parameters->message);

        $event = new MessageSpamChecking(
            $htmlInputProcessor,
            WCF::getUser()->userID ? WCF::getUser() : null,
            UserUtil::getIpAddress(),
        );
        EventHandler::getInstance()->fire($event);
        if ($event->defaultPrevented()) {
            $isDisabled = true;
        }

        $comment = (new \wcf\system\comment\command\CreateComment(
            $objectType,
            $parameters->objectID,
            $htmlInputProcessor,
            WCF::getUser()->userID ? WCF::getUser() : null,
            $username,
            $isDisabled,
        ))();

        FloodControl::getInstance()->registerContent('com.woltlab.wcf.comment');

        return new JsonResponse([
            'commentID' => $comment->commentID,
        ]);
    }
}

/** @internal */
final class CreateCommentParameters
{
    public function __construct(
        /** @var positive-int **/
        public readonly int $objectID,

        /** @var positive-int **/
        public readonly int $objectTypeID,

        /** @var non-empty-string */
        public readonly string $message,

        public readonly string $guestToken,
    ) {
    }
}
