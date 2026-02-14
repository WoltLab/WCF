<?php

namespace wcf\acp\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\language\item\LanguageItem;
use wcf\data\language\item\LanguageItemEditor;
use wcf\data\language\Language;
use wcf\event\language\PhraseChanged;
use wcf\http\Helper;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\dependency\NonEmptyFormFieldDependency;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Handles the editing of a language item.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class LanguageItemEditAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    id: positive-int
                }
                EOT
        );

        $this->assertUserCanEditLanguageItem();

        $languageItem = new LanguageItem($parameters['id']);
        $this->assertLanguageItemExists($languageItem);

        $form = $this->getForm($languageItem);

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $data = $form->getData()['data'];
            if ($languageItem->languageItemOriginIsSystem) {
                unset($data['languageItemValue']);

                $data['languageCustomItemDisableTime'] = null;

                if ($data['languageUseCustomValue']) {
                    $data['languageItemOldValue'] = null;
                }
            }

            if (!$data['languageUseCustomValue'] && !$languageItem->languageCustomItemValue) {
                $data['languageCustomItemValue'] = null;
            }

            $editor = new LanguageItemEditor($languageItem);
            $editor->update($data);

            // clear cache
            LanguageFactory::getInstance()->clearCache();
            LanguageFactory::getInstance()->deleteLanguageCache();

            $language = new Language($languageItem->languageID);
            EventHandler::getInstance()->fire(
                new PhraseChanged($language, $languageItem->languageItem)
            );

            return new JsonResponse([]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    private function assertUserCanEditLanguageItem(): void
    {
        if (!WCF::getSession()->getUser()->userID) {
            throw new PermissionDeniedException();
        }
        if (!WCF::getSession()->getPermission('admin.language.canManageLanguage')) {
            throw new PermissionDeniedException();
        }
    }

    private function assertLanguageItemExists(LanguageItem $languageItem): void
    {
        if (!$languageItem->languageItem) {
            throw new IllegalLinkException();
        }
    }

    private function getForm(LanguageItem $languageItem): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            LanguageItemEditAction::class,
            $languageItem->languageItem
        );
        $form->appendChildren([
            FormContainer::create('languageItemContainer')
                ->appendChildren([
                    MultilineTextFormField::create('languageItemValue')
                        ->label('wcf.acp.language.item.value')
                        ->rows(5)
                        ->immutable((bool)$languageItem->languageItemOriginIsSystem)
                ]),
            FormContainer::create('oldValueContainer')
                ->label('wcf.acp.language.item.oldValue')
                ->available($languageItem->languageItemOriginIsSystem && !empty($languageItem->languageItemOldValue))
                ->description('wcf.acp.language.item.oldValue.description', [
                    'item' => $languageItem
                ])
                ->appendChildren([
                    MultilineTextFormField::create('languageItemOldValue')
                        ->rows(5)
                        ->immutable()
                ]),
            FormContainer::create('customValueContainer')
                ->label('wcf.acp.language.item.customValue')
                ->available((bool)$languageItem->languageItemOriginIsSystem)
                ->appendChildren([
                    BooleanFormField::create('languageUseCustomValue')
                        ->label('wcf.acp.language.item.useCustomValue'),
                    MultilineTextFormField::create('languageCustomItemValue')
                        ->rows(5)
                        ->addDependency(
                            NonEmptyFormFieldDependency::create('languageUseCustomValue')
                                ->fieldId('languageUseCustomValue')
                        ),
                ]),
        ]);

        $form->markRequiredFields(false);
        $form->updatedObject($languageItem);
        $form->build();

        return $form;
    }
}
