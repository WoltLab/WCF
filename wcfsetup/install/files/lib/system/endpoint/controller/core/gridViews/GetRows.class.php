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
use wcf\system\view\grid\AbstractGridView;

#[GetRequest('/core/gridViews/rows')]
final class GetRows implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $parameters = Helper::mapApiParameters($request, GetRowsParameters::class);

        if (!\is_subclass_of($parameters->gridView, AbstractGridView::class)) {
            throw new UserInputException('gridView', 'invalid');
        }

        $view = new $parameters->gridView();
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

        $filterLabels = [];
        foreach (\array_keys($parameters->filters) as $key) {
            $filterLabels[$key] = $view->getFilterLabel($key);
        }

        return new JsonResponse([
            'template' => $view->renderRows(),
            'pages' => $view->countPages(),
            'filterLabels' => $filterLabels,
        ]);
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
        public readonly array $filters
    ) {}
}
