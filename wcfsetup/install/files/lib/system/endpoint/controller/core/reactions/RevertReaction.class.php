<?php

namespace wcf\system\endpoint\controller\core\reactions;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\like\IRestrictedLikeObjectTypeProvider;
use wcf\data\like\Like;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\object\LikeObject;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\reaction\ReactionHandler;
use wcf\system\WCF;

/**
 * Reverts a reaction on an object.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/reactions/revert')]
final class RevertReaction implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $parameters = Helper::mapApiParameters($request, RevertReactionParameters::class);

        $this->assertModuleEnabled();
        $this->assertUserCanReact();

        $objectType = ReactionHandler::getInstance()->getObjectType($parameters->objectType);
        if ($objectType === null) {
            throw new UserInputException('objectType');
        }

        $objectTypeProvider = $objectType->getProcessor();
        $likeable = $objectTypeProvider->getObjectByID($parameters->objectID);
        \assert($likeable instanceof ILikeObject);
        $likeable->setObjectType($objectType);

        if ($objectTypeProvider instanceof IRestrictedLikeObjectTypeProvider) {
            if (!$objectTypeProvider->canLike($likeable)) {
                throw new PermissionDeniedException();
            }
        } elseif (!$objectTypeProvider->checkPermissions($likeable)) {
            throw new PermissionDeniedException();
        }

        if ($likeable->getUserID() == WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }

        $like = Like::getLike(
            $likeable->getObjectType()->objectTypeID,
            $likeable->getObjectID(),
            WCF::getUser()->userID
        );

        if ($like->likeID) {
            (new \wcf\command\reaction\RevertReaction(
                $like,
                $likeable
            ))();
        }

        $likeObject = LikeObject::getLikeObject(
            $likeable->getObjectType()->objectTypeID,
            $likeable->getObjectID()
        );

        return new JsonResponse([
            'reactions' => $likeObject->getCachedReactions(),
        ]);
    }

    private function assertModuleEnabled(): void
    {
        if (!\MODULE_LIKE) {
            throw new IllegalLinkException();
        }
    }

    private function assertUserCanReact(): void
    {
        if (!WCF::getUser()->userID || !WCF::getSession()->hasPermission('user.like.canLike')) {
            throw new PermissionDeniedException();
        }
    }
}

/** @internal */
final class RevertReactionParameters
{
    public function __construct(
        /** @var positive-int */
        public readonly int $objectID,

        /** @var non-empty-string */
        public readonly string $objectType,
    ) {}
}
