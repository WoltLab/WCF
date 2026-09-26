<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\event\object\filter\ObjectFilterBuilderCollecting;
use wcf\http\Helper;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\object\filter\builder\IObjectFilterBuilder;

/**
 * Provides the dialog to configure a single filter of an object filter builder.
 *
 * The builder is selected through the `identifier` query parameter. A GET
 * request returns the dialog, a POST request validates it and responds with
 * the identifier of the chosen filter, a human-readable summary and the
 * serialized value of the filter.
 *
 * @author      Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ObjectFilterBuilderAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    identifier: string,
                }
                EOT
        );

        $event = new ObjectFilterBuilderCollecting();
        EventHandler::getInstance()->fire($event);

        $builder = $event->getBuilders()[$parameters['identifier']] ?? null;
        if ($builder === null) {
            throw new IllegalLinkException();
        }

        if (!$builder->isAccessible()) {
            throw new PermissionDeniedException();
        }

        $form = $this->getForm($builder);

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $rawData = $form->getData();
            $data = $rawData['data'];

            $result = [];
            foreach ($builder->getFilters() as $filter) {
                if ($data['filter'] !== $filter->getIdentifier()) {
                    continue;
                }

                $id = $filter->getFormField()->getId();
                $serializedValue = $filter->serializeValue($data[$id] ?? $rawData[$id]);

                $result = [
                    'identifier' => $filter->getIdentifier(),
                    // Summarize the restored value to match the summaries of the stored filters.
                    'summary' => $filter->summarizeValue($filter->unserializeValue($serializedValue)),
                    'value' => $serializedValue,
                ];
            }

            return new JsonResponse([
                'result' => $result,
            ]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    /**
     * Builds the dialog that offers a selection of the available filters.
     * The form field of each filter is only shown while it is selected.
     *
     * @param IObjectFilterBuilder<*> $builder
     */
    private function getForm(IObjectFilterBuilder $builder): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            static::class,
            'TODO: title',
        );

        $container = FormContainer::create('container');
        $container->addClass('objectFilterBuilder__list');
        $form->appendChild($container);

        $select = SelectFormField::create('filter')
            ->label('TODO: type')
            ->required();
        $container->appendChild($select);

        $selectValues = [];
        foreach ($builder->getFilters() as $filter) {
            $formField = $filter->getFormField();
            $formField->addDependency(
                ValueFormFieldDependency::create($formField->getId() . 'Dependency')
                    ->field($select)
                    ->values([$filter->getIdentifier()])
            );

            $formField->addClass('objectFilterBuilder__list__item');
            $container->appendChild($formField);

            $selectValues[$filter->getIdentifier()] = $filter->getTitle();
        }

        $select->options($selectValues);

        $form->markRequiredFields(false);
        $form->build();

        return $form;
    }
}
