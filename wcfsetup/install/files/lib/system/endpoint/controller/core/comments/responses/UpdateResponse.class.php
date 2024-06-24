<?php

namespace wcf\system\endpoint\controller\core\comments\responses;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\comment\response\CommentResponse;
use wcf\event\message\MessageSpamChecking;
use wcf\http\Helper;
use wcf\system\comment\CommentHandler;
use wcf\system\endpoint\controller\core\comments\TCommentMessageValidator;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\event\EventHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;
use wcf\util\UserUtil;

/**
 * API endpoint for the update of responses.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
#[PostRequest('/core/comments/responses/{id:\d+}')]
final class UpdateResponse implements IController
{
    use TCommentMessageValidator;

    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $response = Helper::fetchObjectFromRequestParameter($variables['id'], CommentResponse::class);

        $this->assertResponseIsEditable($response);

        $parameters = Helper::mapApiParameters($request, UpdateCommentParameters::class);

        $htmlInputProcessor = $this->validateMessage($parameters->message, true, $response->responseID);

        $event = new MessageSpamChecking(
            $htmlInputProcessor,
            WCF::getUser()->userID ? WCF::getUser() : null,
            UserUtil::getIpAddress(),
        );
        EventHandler::getInstance()->fire($event);
        if ($event->defaultPrevented()) {
            throw new PermissionDeniedException();
        }

        (new \wcf\system\comment\response\command\UpdateResponse(
            $response,
            $htmlInputProcessor,
        ))();

        return new JsonResponse([]);
    }

    private function assertResponseIsEditable(CommentResponse $response): void
    {
        $manager = CommentHandler::getInstance()->getCommentManagerByID($response->getComment()->objectTypeID);
        if (!$manager->canEditResponse($response)) {
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
