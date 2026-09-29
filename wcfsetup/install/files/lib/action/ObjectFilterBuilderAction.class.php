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
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\object\filter\builder\IObjectFilterBuilder;
use wcf\system\WCF;

/**
 * Provides the dialog to configure a single filter of an object filter builder.
 *
 * The builder is selected through the `identifier` query parameter. A GET
 * request returns the dialog, a POST request validates it and responds with
 * the identifier of the chosen filter, a human-readable summary and the
 * serialized value of the filter.
 *
 * An existing filter is edited by passing its identifier and its serialized
 * value through the `filter` and `value` query parameters, which prefill the dialog.
 * The identifiers of the other configured filters are passed through `used`,
 * filters that are not repeatable are not offered again.
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
                    filter?: string,
                    value?: string,
                    used?: list<string>,
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

        $form = $this->getForm(
            $builder,
            $parameters['filter'] ?? null,
            $parameters['value'] ?? null,
            $parameters['used'] ?? [],
        );

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
     * If a filter identifier and its serialized value are given, the dialog is
     * prefilled with them. Filters that are not repeatable are only offered if
     * they are not part of the given identifiers of the other configured filters.
     *
     * @param IObjectFilterBuilder<*> $builder
     * @param list<string> $usedIdentifiers
     */
    private function getForm(
        IObjectFilterBuilder $builder,
        ?string $filterIdentifier,
        ?string $serializedValue,
        array $usedIdentifiers,
    ): Psr15DialogForm {
        $form = new Psr15DialogForm(
            static::class,
            WCF::getLanguage()->get(
                $filterIdentifier === null ? 'wcf.objectFilter.addFilter' : 'wcf.objectFilter.editFilter'
            ),
        );

        $container = FormContainer::create('container');
        $container->addClass('objectFilterBuilder__list');
        $form->appendChild($container);

        $select = SelectFormField::create('filter')
            ->label('wcf.objectFilter.filter')
            ->required();
        $container->appendChild($select);

        $selectValues = [];
        foreach ($builder->getFilters() as $filter) {
            if (!$filter->isAvailable()) {
                continue;
            }
            if (!$filter->isRepeatable() && \in_array($filter->getIdentifier(), $usedIdentifiers, true)) {
                continue;
            }

            $formField = $filter->getFormField();
            $formField->addDependency(
                ValueFormFieldDependency::create($formField->getId() . 'Dependency')
                    ->field($select)
                    ->values([$filter->getIdentifier()])
            );

            $formField->addClass('objectFilterBuilder__list__item');
            $container->appendChild($formField);

            $selectValues[$filter->getIdentifier()] = $filter->getTitle();

            if ($filter->getIdentifier() === $filterIdentifier && $serializedValue !== null) {
                if ($formField instanceof MultipleSelectionFormField) {
                    // Keep the remaining selection if some of the selected objects
                    // have been deleted in the meantime.
                    $formField->ignoreInvalidValues();
                }

                try {
                    $formField->value($filter->toFormFieldValue($filter->unserializeValue($serializedValue)));
                } catch (\InvalidArgumentException) {
                    // The value is no longer valid, e.g. a selected object has
                    // been deleted in the meantime, requiring a new value.
                }
            }
        }

        $collator = new \Collator(WCF::getLanguage()->getLocale());
        \uasort(
            $selectValues,
            static fn(string $a, string $b) => $collator->compare($a, $b)
        );

        $select->options($selectValues);
        if ($filterIdentifier !== null && isset($selectValues[$filterIdentifier])) {
            $select->value($filterIdentifier);
        }

        $form->markRequiredFields(false);
        $form->build();

        return $form;
    }
}
