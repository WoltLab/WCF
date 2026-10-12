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
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\gridView\AbstractGridView;
use wcf\system\WCF;

/**
 * Handles the column configuration dialog of grid views.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class GridViewColumnConfigurationAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    gridView: string,
                    columns: list<string>,
                    gridViewParameters: array<string, string|string[]>
                }
                EOT
        );

        if (!\is_subclass_of($parameters['gridView'], AbstractGridView::class)) {
            throw new UserInputException('gridView', 'invalid');
        }

        try {
            /** @var AbstractGridView<DatabaseObject, DatabaseObjectList<DatabaseObject>> $view */
            $view = new $parameters['gridView'](...$parameters['gridViewParameters']);
        } catch (\ArgumentCountError | \TypeError $e) {
            if (\ENABLE_DEBUG_MODE !== 0) {
                throw $e;
            } else {
                throw new IllegalLinkException();
            }
        }

        if (!$view->isAccessible()) {
            throw new PermissionDeniedException();
        }

        if (!$view->isColumnConfigurable()) {
            throw new IllegalLinkException();
        }

        if ($parameters['columns'] !== []) {
            $view->setVisibleColumnIDs($parameters['columns']);
        }

        $form = $this->getForm($view);

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $selectedColumnIDs = $form->getData()['columns'] ?? [];

            // The title column is always visible, including it ensures that
            // the list of visible columns is never empty.
            $columns = [];
            $isDefault = true;
            foreach ($view->getColumns() as $column) {
                $isVisible = $column->isTitleColumn() || \in_array($column->getID(), $selectedColumnIDs, true);
                if ($isVisible) {
                    $columns[] = $column->getID();
                }

                if (!$column->isTitleColumn() && $isVisible === $column->isHidden()) {
                    $isDefault = false;
                }
            }

            return new JsonResponse([
                // `null` represents the default configuration which is
                // omitted from the url.
                'result' => $isDefault ? null : $columns,
            ]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    /**
     * @param AbstractGridView<DatabaseObject, DatabaseObjectList<DatabaseObject>> $gridView
     */
    private function getForm(AbstractGridView $gridView): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            static::class,
            WCF::getLanguage()->get('wcf.global.gridView.columns')
        );

        $options = [];
        $visibleColumnIDs = [];
        foreach ($gridView->getConfigurableColumns() as $column) {
            $options[$column->getID()] = $column->getLabel();

            if ($gridView->isColumnVisible($column)) {
                $visibleColumnIDs[] = $column->getID();
            }
        }

        $form->appendChild(
            MultipleSelectionFormField::create('columns')
                ->options($options, false, false)
                ->value($visibleColumnIDs)
        );

        $form->markRequiredFields(false);
        $form->build();

        return $form;
    }
}
