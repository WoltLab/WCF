<?php

namespace wcf\system\endpoint\controller\core\listViews;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\http\Helper;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\listView\AbstractListView;

/**
 * Retrieves the HTML code for the rendering of a list view in a dialog.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
#[GetRequest('/core/list-views/render')]
final class RenderListView implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $parameters = Helper::mapApiParameters($request, RenderListViewParameters::class);

        if (!\is_subclass_of($parameters->listView, AbstractListView::class)) {
            throw new UserInputException('listView', 'invalid');
        }

        $view = new $parameters->listView(...$parameters->listViewParameters);
        // @phpstan-ignore function.alreadyNarrowedType, instanceof.alwaysTrue
        \assert($view instanceof AbstractListView);

        if (!$view->isAccessible()) {
            throw new PermissionDeniedException();
        }

        $view->setPageNo($parameters->pageNo);
        if ($parameters->sortField !== '') {
            $view->setSortField($parameters->sortField);
        }
        if ($parameters->sortOrder !== '') {
            $view->setSortOrder($parameters->sortOrder);
        }

        if ($parameters->filters !== []) {
            $view->setActiveFilters($parameters->filters);
        }

        return new JsonResponse([
            'listView' => $view->render(),
        ]);
    }
}

/** @internal */
final class RenderListViewParameters
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $listView,
        /** @var positive-int */
        public readonly int $pageNo,
        public readonly string $sortField,
        public readonly string $sortOrder,
        /** @var array<string, string|int> */
        public readonly array $filters,
        /** @var array<string, string|string[]> */
        public readonly array $listViewParameters,
    ) {}
}
