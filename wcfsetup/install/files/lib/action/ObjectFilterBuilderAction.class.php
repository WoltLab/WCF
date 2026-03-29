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

            $result = [];
            foreach ($builder->getFilters() as $filter) {
                if ($data['filter'] !== $filter->getIdentifier()) {
                    continue;
                }

                $id = $filter->getFormField()->getId();
                $value = $data[$id] ?? $rawData[$id];

                $result = [
                    'identifier' => $filter->getIdentifier(),
                    'summary' => $filter->summarizeValue($value),
                    'value' => $filter->serializeValue($value),
                ];
            }

            return new JsonResponse([
                'result' => $result,
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
