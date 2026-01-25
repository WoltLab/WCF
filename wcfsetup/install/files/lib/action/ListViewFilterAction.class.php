<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\container\RowFormContainer;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\listView\AbstractListView;
use wcf\system\WCF;

/**
 * Handles the filter dialog of list views.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class ListViewFilterAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    listView: string,
                    filters: string[],
                    listViewParameters: array<string, string|string[]>
                }
                EOT
        );

        if (!\is_subclass_of($parameters['listView'], AbstractListView::class)) {
            throw new UserInputException('listView', 'invalid');
        }

        /** @var AbstractListView<DatabaseObject, DatabaseObjectList<DatabaseObject>> $view */
        $view = new $parameters['listView'](...$parameters['listViewParameters']);

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

            $rawData = $form->getData();
            $data = $rawData['data'];

            foreach ($view->getAvailableFilters() as $filter) {
                if (!isset($rawData[$filter->getFormDataId()])) {
                    continue;
                }

                $data[$filter->getId()] = $filter->serializeValue($rawData[$filter->getFormDataId()]);
            }

            foreach ($data as $key => $value) {
                if ($value === '' || $value === null || $value === 0) {
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

    /**
     * @param AbstractListView<DatabaseObject, DatabaseObjectList<DatabaseObject>> $listView
     * @param array<string, mixed> $values
     */
    private function getForm(AbstractListView $listView, array $values): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            static::class,
            WCF::getLanguage()->get('wcf.global.filter')
        );

        $container = FormContainer::create('container');
        $container->addClass('listView__filter__list');
        $form->appendChild($container);

        foreach ($listView->getAvailableFilters() as $filter) {
            $formField = $filter->getFormField();

            if (isset($values[$filter->getID()])) {
                $value = $filter->unserializeValue($values[$filter->getID()]);
                $formField->value($value);
            }

            $formField->addClass('listView__filter__item');
            $container->appendChild($formField);
        }

        $form->markRequiredFields(false);
        $form->build();

        return $form;
    }
}
