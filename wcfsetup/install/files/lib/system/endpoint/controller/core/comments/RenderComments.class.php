<?php

namespace wcf\system\endpoint\controller\core\comments;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\comment\StructuredCommentList;
use wcf\http\Helper;
use wcf\system\comment\CommentHandler;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * API endpoint for loading additional rendered comments.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
#[GetRequest('/core/comments/render')]
final class RenderComments implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $parameters = Helper::mapApiParameters($request, RenderCommentsParameters::class);
        $objectType = CommentHandler::getInstance()->getObjectType($parameters->objectTypeID);
        if ($objectType === null) {
            throw new UserInputException('objectTypeID');
        }

        $commentManager = CommentHandler::getInstance()->getCommentManagerByID($parameters->objectTypeID);
        if (!$commentManager->isAccessible($parameters->objectID)) {
            throw new PermissionDeniedException();
        }

        $commentList = $this->getCommentList(
            $parameters->objectTypeID,
            $parameters->objectID,
            $parameters->lastCommentTime
        );

        CommentHandler::getInstance()->markNotificationsAsConfirmedForComments(
            $objectType->objectType,
            $commentList->getObjects()
        );

        return new JsonResponse([
            'lastCommentTime' => $commentList->getMinCommentTime(),
            'template' => WCF::getTPL()->fetch('commentList', 'wcf', [
                'commentList' => $commentList,
                'likeData' => MODULE_LIKE ? $commentList->getLikeData() : [],
            ]),
        ]);
    }

    private function getCommentList(int $objectTypeID, int $objectID, int $lastCommentTime): StructuredCommentList
    {
        $commentList = CommentHandler::getInstance()->getCommentList(
            CommentHandler::getInstance()->getCommentManagerByID($objectTypeID),
            $objectTypeID,
            $objectID,
            false
        );
        if ($lastCommentTime) {
            $commentList->getConditionBuilder()->add("comment.time < ?", [$lastCommentTime]);
        }
        $commentList->readObjects();

        return $commentList;
    }
}

/** @internal */
final class RenderCommentsParameters
{
    public function __construct(
        /** @var positive-int **/
        public readonly int $objectID,

        /** @var positive-int **/
        public readonly int $objectTypeID,

        public readonly int $lastCommentTime = 0,
    ) {
    }
}
