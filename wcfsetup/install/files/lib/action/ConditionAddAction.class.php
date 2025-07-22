<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\Helper;
use wcf\system\condition\provider\AbstractConditionProvider;
use wcf\system\condition\type\IConditionType;
use wcf\system\exception\UserInputException;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\FormDocument;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\WCF;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class ConditionAddAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    provider: non-empty-string,
                    containerId: non-empty-string,
                    index: int,
                }
                EOT
        );

        if (!\is_subclass_of($parameters['provider'], AbstractConditionProvider::class)) {
            throw new UserInputException('provider', 'invalid');
        }

        /** @var AbstractConditionProvider<IConditionType<mixed>> $provider */
        $provider = new $parameters['provider']();

        $form = $this->getForm($provider);

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $data = $form->getData()['data'];
            $condition = $provider->getConditionByIdentifier($data['conditionType']);
            \assert($condition instanceof IConditionType);

            $document = FormDocument::create('tmpForm');

            return new JsonResponse([
                'result' => [
                    'field' => $provider->getConditionFormField($parameters['containerId'], $data['conditionType'], $parameters['index'])
                        ->parent($document)
                        ->getHtml(),
                    'conditionType' => $data['conditionType'],
                ],
            ]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    /**
     * @param AbstractConditionProvider<IConditionType<mixed>> $provider
     */
    private function getForm(AbstractConditionProvider $provider): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            self::class,
            WCF::getLanguage()->get('wcf.condition.add')
        );

        $form->appendChild(
            $this->getConditionTypeFormField()
                ->id('conditionType')
                ->label('wcf.condition.condition')
                ->filterable()
                ->required()
                ->options($this->getOptions($provider), true, false)
        );

        $form->markRequiredFields(false);
        $form->build();

        return $form;
    }

    /**
     * @param AbstractConditionProvider<IConditionType<mixed>> $provider
     *
     * @return array{}
     */
    private function getOptions(AbstractConditionProvider $provider): array
    {
        $conditionTypes = $provider->getConditionTypes();

        $grouped = [];
        foreach ($conditionTypes as $key => $conditionType) {
            $category = $conditionType->getCategory();
            $label = $conditionType->getLabel();

            if (!isset($grouped[$category])) {
                $grouped[$category] = [
                    'items' => [],
                    'label' => WCF::getLanguage()->get('wcf.condition.category.' . $category),
                ];
            }

            $grouped[$category]['items'][$key] = WCF::getLanguage()->get($label);
        }

        $collator = new \Collator(WCF::getLanguage()->getLocale());

        foreach ($grouped as &$category) {
            \uasort($category['items'], static function ($labelA, $labelB) use ($collator) {
                return $collator->compare($labelA, $labelB);
            });
        }
        unset($category);

        \uasort($grouped, static function ($catA, $catB) use ($collator) {
            return $collator->compare($catA['label'], $catB['label']);
        });

        $options = [];

        foreach ($grouped as $categoryKey => $category) {
            $options[] = [
                'depth' => 0,
                'isSelectable' => false,
                'label' => $category['label'],
                'value' => $categoryKey,
            ];

            foreach ($category['items'] as $key => $label) {
                $options[] = [
                    'depth' => 1,
                    'isSelectable' => true,
                    'label' => $label,
                    'value' => $key,
                ];
            }
        }

        return $options;
    }

    private function getConditionTypeFormField(): SingleSelectionFormField
    {
        return new class extends SingleSelectionFormField {
            protected $templateName = 'shared_categorizedSingleSelectionFormField';
        };
    }
}
