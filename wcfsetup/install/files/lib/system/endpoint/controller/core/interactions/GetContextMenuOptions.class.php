<?php

namespace wcf\system\endpoint\controller\core\interactions;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\DatabaseObject;
use wcf\http\Helper;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\UserInputException;
use wcf\system\interaction\IInteractionProvider;
use wcf\system\interaction\InteractionContextMenuView;

/**
 * Retrieves the options for an interaction context menu.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[GetRequest('/core/interactions/context-menu-options')]
final class GetContextMenuOptions implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $parameters = Helper::mapApiParameters($request, GetContextMenuOptionsParameters::class);

        if (!\is_subclass_of($parameters->provider, IInteractionProvider::class)) {
            throw new UserInputException('provider', 'invalid');
        }

        $provider = new $parameters->provider();
        \assert($provider instanceof IInteractionProvider);

        $object = new ($provider->getObjectClassName())($parameters->objectID);
        \assert($object instanceof DatabaseObject);

        $view = new InteractionContextMenuView($provider);

        return new JsonResponse([
            'template' => $view->renderContextMenuOptions($object),
        ]);
    }
}

/** @internal */
final class GetContextMenuOptionsParameters
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $provider,
        public readonly int|string $objectID,
    ) {}
}
