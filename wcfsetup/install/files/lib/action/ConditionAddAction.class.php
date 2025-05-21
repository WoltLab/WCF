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

        /** @var AbstractConditionProvider<IConditionType> $provider */
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
     * @param AbstractConditionProvider<IConditionType> $provider
     */
    private function getForm(AbstractConditionProvider $provider): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            self::class,
            WCF::getLanguage()->get('wcf.condition.add')
        );

        $form->appendChild(
            SingleSelectionFormField::create('conditionType')
                ->label('wcf.condition.condition')
                ->filterable()
                ->required()
                ->options(
                    \array_map(
                        static fn (IConditionType $conditionType) => WCF::getLanguage()->get($conditionType->getLabel()),
                        $provider->getConditionTypes()
                    )
                )
        );

        $form->markRequiredFields(false);
        $form->build();

        return $form;
    }
}
