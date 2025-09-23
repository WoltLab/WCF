<?php

namespace wcf\system\endpoint\controller\core\gridViews;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\http\Helper;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\gridView\AbstractGridView;

/**
 * Retrieves the HTML code for the rendering of a list of grid view rows.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[GetRequest('/core/grid-views/rows')]
final class GetRows implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $parameters = Helper::mapApiParameters($request, GetRowsParameters::class);

        if (!\is_subclass_of($parameters->gridView, AbstractGridView::class)) {
            throw new UserInputException('gridView', 'invalid');
        }

        $view = new $parameters->gridView(...$parameters->gridViewParameters);
        // @phpstan-ignore function.alreadyNarrowedType, instanceof.alwaysTrue
        \assert($view instanceof AbstractGridView);

        if (!$view->isAccessible()) {
            throw new PermissionDeniedException();
        }

        $view->setPageNo($parameters->pageNo);
        if ($parameters->sortField) {
            $view->setSortField($parameters->sortField);
        }
        if ($parameters->sortOrder) {
            $view->setSortOrder($parameters->sortOrder);
        }

        if ($parameters->filters !== []) {
            $view->setActiveFilters($parameters->filters);
        }

        return new JsonResponse([
            'template' => $view->renderRows(),
            'pages' => $view->countPages(),
            'totalRows' => $view->countRows(),
            'filterLabels' => $this->getFilterLabels($view, \array_keys($parameters->filters)),
        ]);
    }

    /**
     * @param list<string> $ids
     * @return array<string, string>
     */
    private function getFilterLabels(AbstractGridView $view, array $ids): array
    {
        $filterLabels = [];
        foreach ($ids as $id) {
            $filterLabels[$id] = $view->getFilterLabel($id);
        }

        return $filterLabels;
    }
}

/** @internal */
final class GetRowsParameters
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $gridView,
        public readonly int $pageNo,
        public readonly string $sortField,
        public readonly string $sortOrder,
        /** @var string[] */
        public readonly array $filters,
        /** @var array<string, string|string[]> */
        public readonly array $gridViewParameters,
    ) {}
}
