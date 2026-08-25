<?php

namespace wcf\system\endpoint\controller\core\users\objectWatches;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\user\object\watch\IUserObjectWatch;
use wcf\system\WCF;

/**
 * Removes the subscription of the active user to a watchable object.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/users/object-watches/unsubscribe')]
final class Unsubscribe implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $parameters = Helper::mapApiParameters($request, UnsubscribeParameters::class);

        $this->assertUserIsLoggedIn();

        $objectType = $this->getObjectType($parameters->objectType);
        $this->getProcessor($objectType)->validateObjectID($parameters->objectID);

        new \wcf\command\user\object\watch\Unsubscribe(
            $objectType,
            $parameters->objectID,
            WCF::getUser(),
        )();

        return new JsonResponse([]);
    }

    private function assertUserIsLoggedIn(): void
    {
        if (WCF::getUser()->isGuest()) {
            throw new PermissionDeniedException();
        }
    }

    private function getObjectType(string $objectType): ObjectType
    {
        $objectTypeObj = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.user.objectWatch',
            $objectType
        );
        if ($objectTypeObj === null) {
            throw new UserInputException('objectType');
        }

        return $objectTypeObj;
    }

    private function getProcessor(ObjectType $objectType): IUserObjectWatch
    {
        $processor = $objectType->getProcessor();
        \assert($processor instanceof IUserObjectWatch);

        return $processor;
    }
}

/** @internal */
final class UnsubscribeParameters
{
    public function __construct(
        /** @var positive-int */
        public readonly int $objectID,

        /** @var non-empty-string */
        public readonly string $objectType,
    ) {}
}
