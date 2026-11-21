<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\command\textModule\CreateTextModule;
use wcf\command\textModule\UpdateTextModule;
use wcf\data\text\module\TextModule;
use wcf\data\text\module\TextModuleBuilder;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\language\ContentLanguageFormField;
use wcf\system\form\builder\field\TitleFormField;
use wcf\system\form\builder\field\wysiwyg\WysiwygFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\Psr15BuilderDialogForm;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Creates or edits a text module in a dialog.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class TextModuleEditAction implements RequestHandlerInterface
{
    private ?TextModule $textModule = null;

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!TextModule::canUse()) {
            throw new PermissionDeniedException();
        }

        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    id?: positive-int
                }
                EOT
        );

        if (isset($parameters['id'])) {
            $textModule = new TextModule($parameters['id']);
            if ($textModule->isNil()) {
                throw new IllegalLinkException();
            }

            $this->textModule = $textModule;
        }

        $form = $this->getForm($request->getMethod() === 'GET');

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            if ($this->textModule === null) {
                $builder = TextModuleBuilder::forCreate()
                    ->setUser(WCF::getUser());
            } else {
                $builder = TextModuleBuilder::forUpdate($this->textModule);
            }

            $form->applyValuesToBuilder($builder);

            if ($this->textModule === null) {
                $textModule = new CreateTextModule($builder)();
            } else {
                $textModule = new UpdateTextModule($builder)();
            }

            return new JsonResponse([
                'result' => [
                    'textModuleID' => $textModule->textModuleID,
                    'title' => $textModule->getTitle(),
                ],
            ]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    private function getForm(bool $loadValues): Psr15BuilderDialogForm
    {
        $form = new Psr15BuilderDialogForm(
            static::class,
            WCF::getLanguage()->get($this->textModule === null ? 'wcf.textModule.add' : 'wcf.textModule.edit')
        );

        $form->appendChildren([
            FormContainer::create('general')
                ->appendChildren([
                    TitleFormField::create()
                        ->required()
                        ->autoFocus()
                        ->maximumLength(255)
                        ->saveValueCallback(static function (TextModuleBuilder $builder, TitleFormField $field): void {
                            $builder->setTitle($field->getSaveValue());
                        })
                        ->loadValueCallback(static function (TextModule $object, IFormField $field): void {
                            $field->value($object->title);
                        }),
                    ContentLanguageFormField::create()
                        ->saveValueCallback(static function (TextModuleBuilder $builder, ContentLanguageFormField $field): void {
                            $language = null;
                            if ($field->getValue() !== null) {
                                $language = LanguageFactory::getInstance()->getLanguage($field->getValue());
                            }

                            $builder->setLanguageID($language?->languageID);
                        })
                        ->loadValueCallback(static function (TextModule $object, IFormField $field): void {
                            $field->value($object->languageID);
                        }),
                    WysiwygFormField::create('message')
                        ->label('wcf.textModule.message')
                        ->objectType('com.woltlab.wcf.textModule')
                        ->required()
                        ->saveValueCallback(static function (TextModuleBuilder $builder, WysiwygFormField $field): void {
                            $builder->setMessage($field->getSaveValue());
                        })
                        ->loadValueCallback(static function (TextModule $object, IFormField $field): void {
                            $field->value($object->message);
                        }),
                ]),
        ]);

        if ($this->textModule === null) {
            $form->formMode(IFormDocument::FORM_MODE_CREATE);
            $form->build();
        } else {
            $form->formMode(IFormDocument::FORM_MODE_UPDATE);
            $form->build();
            $form->updatedObject($this->textModule, $loadValues);
        }

        return $form;
    }
}
