<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\view\grid\AbstractGridView;
use wcf\system\WCF;

final class GridViewFilterAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    gridView: string,
                    filters: string[]
                }
                EOT
        );

        if (!\is_subclass_of($parameters['gridView'], AbstractGridView::class)) {
            throw new UserInputException('gridView', 'invalid');
        }

        $view = new $parameters['gridView'];
        \assert($view instanceof AbstractGridView);

        if (!$view->isAccessible()) {
            throw new PermissionDeniedException();
        }

        if (!$view->isFilterable()) {
            throw new IllegalLinkException();
        }

        $form = $this->getForm($view, $parameters['filters']);

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $data = $form->getData()['data'];
            foreach ($data as $key => $value) {
                if ($value === '' || $value === null) {
                    unset($data[$key]);
                }
            }

            return new JsonResponse([
                'result' => $data
            ]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    private function getForm(AbstractGridView $gridView, array $values): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            static::class,
            WCF::getLanguage()->get('wcf.global.filter')
        );

        foreach ($gridView->getFilterableColumns() as $column) {
            $formField = $column->getFilterFormField();

            if (isset($values[$column->getID()])) {
                $formField->value($values[$column->getID()]);
            }

            $form->appendChild($formField);
        }

        $form->markRequiredFields(false);
        $form->build();

        return $form;
    }
}
