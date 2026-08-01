<?php

namespace wcf\system\endpoint\controller\core\reactions;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\like\ILikeObjectTypeProvider;
use wcf\data\like\IRestrictedLikeObjectTypeProvider;
use wcf\data\like\Like;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\object\LikeObject;
use wcf\data\reaction\type\ReactionTypeCache;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\reaction\ReactionHandler;
use wcf\system\WCF;

/**
 * Sets or reverts a reaction on an object.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/reactions/set')]
final class SetReaction implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $parameters = Helper::mapApiParameters($request, SetReactionParameters::class);

        $this->assertModuleEnabled();
        $this->assertUserCanReact();

        $objectType = ReactionHandler::getInstance()->getObjectType($parameters->objectType);
        if ($objectType === null) {
            throw new UserInputException('objectType');
        }

        $reactionType = ReactionTypeCache::getInstance()->getReactionTypeByID($parameters->reactionTypeID);
        if ($reactionType === null) {
            throw new UserInputException('reactionTypeID');
        }

        $objectTypeProvider = $objectType->getProcessor();
        \assert($objectTypeProvider instanceof ILikeObjectTypeProvider);

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

        if (!$reactionType->isAssignable) {
            throw new UserInputException('reactionTypeID');
        }

        $like = Like::getLike(
            $likeable->getObjectType()->objectTypeID,
            $likeable->getObjectID(),
            WCF::getUser()->userID
        );

        if ($like->isNil() || $like->reactionTypeID !== $reactionType->reactionTypeID) {
            new \wcf\command\reaction\SetReaction(
                $likeable,
                WCF::getUser(),
                $reactionType
            )();
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
        if (WCF::getUser()->isGuest() || !WCF::getSession()->hasPermission('user.like.canLike')) {
            throw new PermissionDeniedException();
        }
    }
}

/** @internal */
final class SetReactionParameters
{
    public function __construct(
        /** @var positive-int */
        public readonly int $objectID,

        /** @var non-empty-string */
        public readonly string $objectType,

        /** @var positive-int */
        public readonly int $reactionTypeID,
    ) {}
}
