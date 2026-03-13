<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\object\filter\builder\UserGroupAssignmentObjectFilterBuilder;

final class ObjectFilterBuilderAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /*
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    gridView: string,
                    filters: string[],
                    gridViewParameters: array<string, string|string[]>
                }
                EOT
        );

        if (!\is_subclass_of($parameters['gridView'], AbstractGridView::class)) {
            throw new UserInputException('gridView', 'invalid');
        }

        try {
            /** @var AbstractGridView<DatabaseObject, DatabaseObjectList<DatabaseObject>> $view *//*
            $view = new $parameters['gridView'](...$parameters['gridViewParameters']);
            // @phpstan-ignore catch.neverThrown
        } catch (\ArgumentCountError $e) {
            if (\ENABLE_DEBUG_MODE) {
                throw $e;
            } else {
                throw new IllegalLinkException();
            }
        }
        */

        /*
        if (!$view->isAccessible()) {
            throw new PermissionDeniedException();
        }

        if (!$view->isFilterable()) {
            throw new IllegalLinkException();
        }
        */

        $form = $this->getForm();

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $rawData = $form->getData();
            $data = $rawData['data'];

            $builder = new UserGroupAssignmentObjectFilterBuilder();

            foreach ($builder->getFilters() as $filter) {
                $id = $filter->getFormField()->getId();
                if (!isset($rawData[$id])) {
                    continue;
                }

                $data[$id] = $filter->serializeValue($rawData[$id]);
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

    private function getForm(): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            static::class,
            'TODO: title',
            //WCF::getLanguage()->get('wcf.global.filter')
        );

        $container = FormContainer::create('container');
        $container->addClass('objectFilterBuilder__list');
        $form->appendChild($container);

        $select = SelectFormField::create('filter')
            ->label('TODO: type')
            ->required();
        $container->appendChild($select);

        $builder = new UserGroupAssignmentObjectFilterBuilder();
        $selectValues = [];
        foreach ($builder->getFilters() as $filter) {
            $formField = $filter->getFormField();
            $formField->addDependency(
                ValueFormFieldDependency::create($formField->getId() . 'Dependency')
                    ->field($select)
                    ->values([$filter->getIdentifier()])
            );

            /*
            if (isset($values[$filter->getID()])) {
                $value = $filter->unserializeValue($values[$filter->getID()]);
                $formField->value($value);
            }
            */

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
