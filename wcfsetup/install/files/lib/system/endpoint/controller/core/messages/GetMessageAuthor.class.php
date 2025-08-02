<?php

namespace wcf\system\endpoint\controller\core\messages;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\DatabaseObject;
use wcf\data\IMessage;
use wcf\http\Helper;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;

/**
 * Returns information about the author of a quoted message.
 *
 * @author    Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.2
 */
#[GetRequest('/core/messages/message-author')]
final class GetMessageAuthor implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $parameters = Helper::mapApiParameters($request, GetMessageAuthorParameters::class);

        // @phpstan-ignore argument.templateType
        $object = Helper::fetchObjectFromRequestParameter($parameters->objectID, $parameters->className);
        \assert($object instanceof IMessage && $object instanceof DatabaseObject);

        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($object->getUserID());

        return new JsonResponse(
            [
                "objectID" => $object->getObjectID(),
                "author" => $userProfile->getUsername(),
                "avatar" => $userProfile->getAvatar()->getURL(),
                "link" => $object->getLink(),
            ],
            200,
            [
                'cache-control' => [
                    'max-age=300',
                ],
            ]
        );
    }
}

/** @internal */
final class GetMessageAuthorParameters
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $className,
        /** @var positive-int */
        public readonly int $objectID,
    ) {}
}
