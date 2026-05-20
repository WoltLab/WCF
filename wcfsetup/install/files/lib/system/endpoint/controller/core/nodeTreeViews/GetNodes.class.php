<?php

namespace wcf\system\endpoint\controller\core\nodeTreeViews;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\http\Helper;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\nodeTreeView\AbstractNodeTreeView;

/**
 * Retrieves the HTML code for the rendering of all node tree view items.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[GetRequest('/core/node-tree-views/nodes')]
final class GetNodes implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $parameters = Helper::mapApiParameters($request, GetNodesParameters::class);

        if (!\is_subclass_of($parameters->nodeTreeView, AbstractNodeTreeView::class)) {
            throw new UserInputException('nodeTreeView', 'invalid');
        }

        $view = new $parameters->nodeTreeView(...$parameters->nodeTreeViewParameters);
        // @phpstan-ignore function.alreadyNarrowedType, instanceof.alwaysTrue
        \assert($view instanceof AbstractNodeTreeView);

        if (!$view->isAccessible()) {
            throw new PermissionDeniedException();
        }

        return new JsonResponse([
            'template' => $view->renderItems(),
        ]);
    }
}

/** @internal */
final class GetNodesParameters
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $nodeTreeView,
        /** @var array<string, string|int|string[]> */
        public readonly array $nodeTreeViewParameters,
    ) {}
}
