<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\command\media\UpdateMedia;
use wcf\data\category\CategoryNodeTree;
use wcf\data\file\File;
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
use wcf\system\form\builder\field\FileProcessorFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\TitleFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\Psr15BuilderDialogForm;
use wcf\system\form\builder\TemplateFormNode;
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

        $form = $this->getForm();

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $builder = MediaBuilder::forUpdate($this->media);
            $form->applyValuesToBuilder($builder);
            $media = (new UpdateMedia($builder))();

            return new JsonResponse([
                'result' => $this->getResult(new Media($media->mediaID)),
            ]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    private function getForm(): Psr15BuilderDialogForm
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
                    SingleSelectionFormField::create('categoryID')
                        ->label('wcf.global.category')
                        ->options($this->getCategoryOptions(), true)
                        ->value(0)
                        ->saveValueCallback(static function (MediaBuilder $builder, SingleSelectionFormField $field): void {
                            $builder->setCategoryID((int)$field->getSaveValue() ?: null);
                        })
                        ->loadValueCallback(static function (Media $object, IFormField $field): void {
                            $field->value($object->categoryID ?? 0);
                        }),
                ]),
            FormContainer::create('content')
                ->label('wcf.global.form.data')
                ->appendChildren([
                    TitleFormField::create('title')
                        ->l10n()
                        ->maximumLength(255)
                        ->saveValueCallback(static function (MediaBuilder $builder, TitleFormField $field): void {
                            $builder->setTitle($field->getL10nValues());
                        })
                        ->loadValueCallback(static function (Media $object, IFormField $field): void {
                            $field->value($object->getL10nValues('title'));
                        }),
                    MultilineTextFormField::create('caption')
                        ->label('wcf.media.caption')
                        ->l10n()
                        ->saveValueCallback(static function (MediaBuilder $builder, MultilineTextFormField $field): void {
                            $builder->setCaption($field->getL10nValues());
                        })
                        ->loadValueCallback(static function (Media $object, IFormField $field): void {
                            $field->value($object->getL10nValues('caption'));
                        }),
                    BooleanFormField::create('captionEnableHtml')
                        ->label('wcf.media.caption.enableHtml')
                        ->saveValueCallback(static function (MediaBuilder $builder, BooleanFormField $field): void {
                            $builder->setCaptionEnableHtml((bool)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (Media $object, IFormField $field): void {
                            $field->value($object->captionEnableHtml);
                        }),
                    TextFormField::create('altText')
                        ->label('wcf.media.altText')
                        ->l10n()
                        ->maximumLength(255)
                        ->saveValueCallback(static function (MediaBuilder $builder, TextFormField $field): void {
                            $builder->setAltText($field->getL10nValues());
                        })
                        ->loadValueCallback(static function (Media $object, IFormField $field): void {
                            $field->value($object->getL10nValues('altText'));
                        }),
                ]),
            FormContainer::create('access')
                ->label('wcf.acl.access')
                ->appendChild(
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
                        })
                ),
        ]);

        $form->updatedObject($this->media);
        $form->build();

        return $form;
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
                'sameFileType',
                static function (FileProcessorFormField $field) use ($media): void {
                    $file = $field->getFiles()[$field->getValue()] ?? null;
                    if ($file === null || $file->fileID === $media->fileID) {
                        return;
                    }

                    if ($file->mimeType !== $media->fileType) {
                        $field->addValidationError(new FormFieldValidationError(
                            'differentFileType',
                            'wcf.media.upload.error.differentFileType'
                        ));
                    }
                }
            ))
            ->saveValueCallback(static function (MediaBuilder $builder, FileProcessorFormField $field) use ($media): void {
                $file = $field->getFiles()[$field->getValue()] ?? null;
                if (!($file instanceof File) || $file->fileID === $media->fileID) {
                    return;
                }

                // The previous file is deleted by the builder.
                $builder->setFile($file)
                    ->setFileUpdateTime(\TIME_NOW);
            })
            ->loadValueCallback(static function (Media $object, IFormField $field): void {
                if ($object->fileID !== null) {
                    $field->value($object->fileID);
                }
            });
    }

    /**
     * @return list<array{depth: int, label: string, value: int}>
     */
    private function getCategoryOptions(): array
    {
        $options = [
            [
                'depth' => 0,
                'label' => WCF::getLanguage()->get('wcf.global.noSelection'),
                'value' => 0,
            ],
        ];

        foreach (new CategoryNodeTree('com.woltlab.wcf.media.category') as $category) {
            $options[] = [
                'depth' => $category->getDepth() - 1,
                'label' => $category->getTitle(),
                'value' => $category->categoryID,
            ];
        }

        return $options;
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
