<?php

namespace wcf\system\endpoint\controller\core\versionTrackers;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\IVersionTrackerObject;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\version\IVersionTrackerProvider;
use wcf\system\version\VersionTracker;

/**
 * Reverts a version tracker object to a previous version.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest('/core/version-trackers/revert')]
final class RevertVersion implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $parameters = Helper::mapApiParameters($request, RevertVersionParameters::class);

        $objectType = VersionTracker::getInstance()->getObjectType($parameters->objectType);
        $processor = $objectType->getProcessor();
        \assert($processor instanceof IVersionTrackerProvider);
        if (!$processor->canAccess()) {
            throw new PermissionDeniedException();
        }

        $object = $processor->getObjectByID($parameters->objectId);
        \assert($object instanceof IVersionTrackerObject);
        if ($object->getObjectID() === 0) {
            throw new UserInputException('objectId');
        }

        $version = VersionTracker::getInstance()->getVersion($parameters->objectType, $parameters->versionId);
        if ($version === null) {
            throw new UserInputException('versionId');
        }

        $processor->revert($object, $version);

        return new JsonResponse([]);
    }
}

/** @internal */
final class RevertVersionParameters
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $objectType,
        public readonly int $objectId,
        public readonly int $versionId,
    ) {}
}
