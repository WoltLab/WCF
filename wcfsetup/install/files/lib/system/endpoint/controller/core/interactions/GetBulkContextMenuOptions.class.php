<?php

namespace wcf\system\endpoint\controller\core\interactions;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\DatabaseObjectList;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\UserInputException;
use wcf\system\interaction\bulk\BulkInteractionContextMenuView;
use wcf\system\interaction\bulk\IBulkInteractionProvider;

/**
 * Retrieves the options for a bulk interaction context menu.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest('/core/interactions/bulk-context-menu-options')]
final class GetBulkContextMenuOptions implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $parameters = Helper::mapApiParameters($request, GetBulkContextMenuOptionsParameters::class);

        if (!\is_subclass_of($parameters->provider, IBulkInteractionProvider::class)) {
            throw new UserInputException('provider', 'invalid');
        }

        $provider = new $parameters->provider();
        \assert($provider instanceof IBulkInteractionProvider);

        $list = new ($provider->getObjectListClassName())();
        \assert($list instanceof DatabaseObjectList);
        $list->setObjectIDs($parameters->objectIDs);
        $list->readObjects();

        $view = new BulkInteractionContextMenuView($provider);

        return new JsonResponse([
            'template' => $view->renderContextMenuOptions($list->getObjects()),
        ]);
    }
}

/** @internal */
final class GetBulkContextMenuOptionsParameters
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $provider,
        /** @var int[] */
        public readonly array $objectIDs,
    ) {}
}
