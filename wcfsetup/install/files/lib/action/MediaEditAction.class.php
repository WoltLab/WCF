<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\command\media\UpdateMedia;
use wcf\data\media\Media;
use wcf\data\media\MediaBuilder;
use wcf\http\Helper;
use wcf\system\acl\simple\SimpleAclHandler;
use wcf\system\cache\runtime\MediaRuntimeCache;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\acl\simple\SimpleAclFormField;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\dependency\EmptyFormFieldDependency;
use wcf\system\form\builder\field\dependency\NonEmptyFormFieldDependency;
use wcf\system\form\builder\field\FileProcessorFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\media\MediaCategoryFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\TitleFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\Psr15BuilderDialogForm;
use wcf\system\form\builder\TemplateFormNode;
use wcf\system\l10n\L10nStorage;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Edits a media file in a dialog.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class MediaEditAction implements RequestHandlerInterface
{
    private Media $media;

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

        $media = MediaRuntimeCache::getInstance()->getObject($parameters['id']);
        if ($media === null) {
            throw new IllegalLinkException();
        }
        if (!$media->canManage()) {
            throw new PermissionDeniedException();
        }
        $this->media = $media;

        $form = $this->getForm($request->getMethod() === 'GET');

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $builder = MediaBuilder::forUpdate($this->media);
            $form->applyValuesToBuilder($builder);

            // The language cannot be chosen if there is only one language.
            if (!$this->isMultilingualismAvailable()) {
                $builder->setLanguageID(LanguageFactory::getInstance()->getDefaultLanguageID());
            }

            $media = (new UpdateMedia($builder))();

            return new JsonResponse([
                'result' => $this->getResult(new Media($media->mediaID)),
            ]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    private function getForm(bool $loadValues): Psr15BuilderDialogForm
    {
        $form = new Psr15BuilderDialogForm(
            static::class,
            WCF::getLanguage()->get('wcf.media.edit')
        );

        $form->appendChildren([
            TemplateFormNode::create('information')
                ->templateName('shared_mediaEditInformation')
                ->variables(['media' => $this->media]),
            FormContainer::create('general')
                ->appendChildren([
                    $this->getFileFormField(),
                    MediaCategoryFormField::create()
                        ->saveValueCallback(static function (MediaBuilder $builder, MediaCategoryFormField $field): void {
                            $builder->setCategoryID($field->getCategoryID());
                        })
                        ->loadValueCallback(static function (Media $object, IFormField $field): void {
                            $field->value($object->categoryID ?? 0);
                        }),
                    ...$this->getContentFormFields(),
                    ...($this->media->isImage !== 0 ? [$this->getCaptionEnableHtmlFormField()] : []),
                    SimpleAclFormField::create('acl')
                        ->saveValueCallback(static function (MediaBuilder $builder, SimpleAclFormField $field): void {
                            $value = $field->getValue();
                            if (!\is_array($value) || $value === []) {
                                return;
                            }

                            $builder->setAclValues([
                                'allowAll' => (bool)($value['allowAll'] ?? true),
                                'user' => \array_values(\array_map(\intval(...), $value['user'] ?? [])),
                                'group' => \array_values(\array_map(\intval(...), $value['group'] ?? [])),
                            ]);
                        })
                        ->loadValueCallback(static function (Media $object, IFormField $field): void {
                            $field->value(
                                SimpleAclHandler::getInstance()->getValues('com.woltlab.wcf.media', $object->mediaID)
                            );
                        }),
                ]),
        ]);

        // The values must be loaded after building the form, because the l10n
        // fields reset their values when they are populated.
        $form->formMode(IFormDocument::FORM_MODE_UPDATE);
        $form->build();
        $form->updatedObject($this->media, $loadValues);

        return $form;
    }

    /**
     * Returns the fields for the title, caption and alternative text. A media
     * file is either assigned to a single language with a single value per
     * field or it is multilingual with values for every language.
     *
     * @return list<IFormField>
     */
    private function getContentFormFields(): array
    {
        $multilingual = $this->isMultilingualismAvailable();

        $fields = [];
        if ($multilingual) {
            $languages = [];
            foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
                $languages[$language->languageID] = $language->languageName;
            }

            $fields[] = BooleanFormField::create('isMultilingual')
                ->label('wcf.media.isMultilingual')
                ->saveValueCallback(static function (MediaBuilder $builder, BooleanFormField $field): void {
                    if ($field->getSaveValue() !== 0) {
                        $builder->setMultilingual();
                    }
                })
                ->loadValueCallback(static function (Media $object, IFormField $field): void {
                    $field->value($object->isMultilingual);
                });
            $fields[] = SingleSelectionFormField::create('languageID')
                ->label('wcf.media.languageID')
                ->options($languages, labelLanguageItems: false)
                ->value(WCF::getLanguage()->languageID)
                ->saveValueCallback(static function (MediaBuilder $builder, SingleSelectionFormField $field): void {
                    $builder->setLanguageID((int)$field->getSaveValue());
                })
                ->loadValueCallback(static function (Media $object, IFormField $field): void {
                    $field->value($object->getLanguageID() ?? WCF::getLanguage()->languageID);
                })
                ->addDependency(
                    EmptyFormFieldDependency::create('isMultilingual')
                        ->fieldId('isMultilingual')
                );
        }

        foreach ($this->getContentFields() as $columnName => $field) {
            $field->saveValueCallback(static function (MediaBuilder $builder, IFormField $field) use ($columnName): void {
                $value = [L10nStorage::MONOLINGUAL => (string)$field->getSaveValue()];

                match ($columnName) {
                    'title' => $builder->setTitle($value),
                    'caption' => $builder->setCaption($value),
                    'altText' => $builder->setAltText($value),
                };
            })->loadValueCallback(static function (Media $object, IFormField $field) use ($columnName): void {
                $field->value(L10nStorage::resolveValue(
                    $object->getL10nValues($columnName),
                    $object->getLanguageID()
                ));
            });

            if ($multilingual) {
                $field->addDependency(
                    EmptyFormFieldDependency::create('isMultilingual')
                        ->fieldId('isMultilingual')
                );
            }

            $fields[] = $field;
        }

        if (!$multilingual) {
            return $fields;
        }

        foreach ($this->getContentFields('I18n') as $columnName => $field) {
            $field->l10nRequired()
                ->saveValueCallback(static function (MediaBuilder $builder, TextFormField $field) use ($columnName): void {
                    $values = $field->getL10nValues();

                    match ($columnName) {
                        'title' => $builder->setTitle($values),
                        'caption' => $builder->setCaption($values),
                        'altText' => $builder->setAltText($values),
                    };
                })
                ->loadValueCallback(static function (Media $object, IFormField $field) use ($columnName): void {
                    $values = $object->getL10nValues($columnName);
                    if (isset($values[L10nStorage::MONOLINGUAL])) {
                        $value = $values[L10nStorage::MONOLINGUAL];

                        $values = [];
                        foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
                            $values[$language->languageID] = $value;
                        }
                    }

                    $field->value($values);
                })
                ->addDependency(
                    NonEmptyFormFieldDependency::create('isMultilingual')
                        ->fieldId('isMultilingual')
                );

            $fields[] = $field;
        }

        return $fields;
    }

    /**
     * Returns the fields for the title, caption and alternative text with the
     * given id suffix. The caption and alternative text are only available
     * for images.
     *
     * @return array<string, TextFormField>
     */
    private function getContentFields(string $idSuffix = ''): array
    {
        $fields = [
            'title' => TitleFormField::create('title' . $idSuffix)
                ->maximumLength(255),
        ];

        if ($this->media->isImage !== 0) {
            $fields['caption'] = MultilineTextFormField::create('caption' . $idSuffix)
                ->label('wcf.media.caption');
            $fields['altText'] = TextFormField::create('altText' . $idSuffix)
                ->label('wcf.media.altText')
                ->maximumLength(255);
        }

        return $fields;
    }

    private function getCaptionEnableHtmlFormField(): BooleanFormField
    {
        return BooleanFormField::create('captionEnableHtml')
            ->label('wcf.media.caption.enableHtml')
            ->saveValueCallback(static function (MediaBuilder $builder, BooleanFormField $field): void {
                $builder->setCaptionEnableHtml($field->getSaveValue() !== 0);
            })
            ->loadValueCallback(static function (Media $object, IFormField $field): void {
                $field->value($object->captionEnableHtml);
            });
    }

    private function isMultilingualismAvailable(): bool
    {
        return \count(LanguageFactory::getInstance()->getLanguages()) > 1;
    }

    private function getFileFormField(): FileProcessorFormField
    {
        $media = $this->media;

        return FileProcessorFormField::create('file')
            ->objectType('com.woltlab.wcf.media')
            ->label('wcf.media.file')
            ->description('wcf.media.file.description')
            ->singleFileUpload()
            ->simpleReplace()
            ->hideDeleteButton()
            ->context(['mediaID' => $media->mediaID])
            ->addValidator(new FormFieldValidator(
                'assigned',
                static function (FileProcessorFormField $field) use ($media): void {
                    $file = $field->getFiles()[$field->getValue()] ?? null;
                    if ($file === null || $file->fileID === $media->fileID) {
                        return;
                    }

                    // The upload assigns the replacement right away, see
                    // `MediaFileProcessor::adopt()`. Any other file has not
                    // been uploaded for this media file and must not be claimed.
                    $field->addValidationError(new FormFieldValidationError(
                        'adopt',
                        'wcf.upload.error.adopt',
                        ['filename' => $file->filename]
                    ));
                }
            ))
            ->loadValueCallback(static function (Media $object, IFormField $field): void {
                if ($object->fileID !== null) {
                    $field->value($object->fileID);
                }
            });
    }

    /**
     * @return array{
     *  mediaID: int,
     *  categoryID: ?int,
     *  title: string,
     *  filename: string,
     *  elementTag: string,
     * }
     */
    private function getResult(Media $media): array
    {
        return [
            'mediaID' => $media->mediaID,
            'categoryID' => $media->categoryID,
            'title' => $media->getTitle(),
            'filename' => $media->filename,
            'elementTag' => $media->getElementTag(144),
        ];
    }
}
