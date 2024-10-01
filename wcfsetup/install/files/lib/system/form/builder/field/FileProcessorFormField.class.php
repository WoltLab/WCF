<?php

namespace wcf\system\form\builder\field;

use wcf\data\file\File;
use wcf\data\file\FileList;
use wcf\data\file\thumbnail\FileThumbnailList;
use wcf\system\file\processor\FileProcessor;
use wcf\system\file\processor\IFileProcessor;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\TObjectTypeFormNode;
use wcf\system\style\IFontAwesomeIcon;
use wcf\util\ArrayUtil;
use wcf\util\ImageUtil;

/**
 * Form field for file processors.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class FileProcessorFormField extends AbstractFormField
{
    use TObjectTypeFormNode;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_fileProcessorFormField';

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/FileProcessor';

    private array $context = [];

    /**
     * @var File[]
     */
    private array $files = [];
    private bool $singleFileUpload = false;
    private bool $bigPreview = false;
    private array $actionButtons = [];

    #[\Override]
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());

            if ($this->isSingleFileUpload()) {
                $this->value(\intval($value));
            } else {
                $this->value(ArrayUtil::toIntegerArray($value));
            }
        }

        return $this;
    }

    #[\Override]
    public function hasSaveValue()
    {
        return $this->isSingleFileUpload();
    }

    #[\Override]
    public function getHtmlVariables()
    {
        return [
            'fileProcessorHtmlElement' => FileProcessor::getInstance()->getHtmlElement(
                $this->getFileProcessor(),
                $this->context
            ),
            'maxUploads' => $this->getFileProcessor()->getMaximumCount($this->context),
            'actionButtons' => $this->actionButtons,
        ];
    }

    #[\Override]
    public function populate()
    {
        parent::populate();

        if ($this->isSingleFileUpload()) {
            return $this;
        }

        $this->getDocument()->getDataHandler()->addProcessor(
            new CustomFormDataProcessor(
                'fileProcessor',
                function (IFormDocument $document, array $parameters) {
                    $parameters[$this->getObjectProperty()] = $this->getValue();

                    return $parameters;
                }
            )
        );

        return $this;
    }

    public function getFileProcessor(): IFileProcessor
    {
        return $this->getObjectType()->getProcessor();
    }

    public function isSingleFileUpload(): bool
    {
        return $this->singleFileUpload;
    }

    /**
     * Sets whether only a single file can be uploaded.
     * If set to true, the value of the field will be an integer.
     * Otherwise, the value will be an array of integers.
     */
    public function singleFileUpload(bool $singleFileUpload = true): self
    {
        if (!$singleFileUpload && $this->bigPreview) {
            throw new \InvalidArgumentException(
                "Single file upload can't be disabled if the big preview is enabled for the field '{$this->getId()}'."
            );
        }

        $this->singleFileUpload = $singleFileUpload;

        return $this;
    }

    #[\Override]
    public function getObjectTypeDefinition()
    {
        return 'com.woltlab.wcf.file';
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    #[\Override]
    public function value($value)
    {
        $fileIDs = [];
        if ($this->isSingleFileUpload()) {
            $file = new File($value);
            if ($file->fileID === $value) {
                $this->files = [$file->fileID => $file];
                $fileIDs[] = $value;
            } else {
                $value = null;
            }
        } else {
            if (!\is_array($value)) {
                $value = [$value];
            }

            $fileList = new FileList();
            $fileList->setObjectIDs($value);
            $fileList->readObjects();
            $this->files = $fileList->getObjects();

            // remove obsolete file IDs from $value
            $fileIDs = $value = $fileList->getObjectIDs();
        }

        if ($fileIDs !== []) {
            $thumbnailList = new FileThumbnailList();
            $thumbnailList->getConditionBuilder()->add("fileID IN (?)", [$fileIDs]);
            $thumbnailList->readObjects();
            foreach ($thumbnailList as $thumbnail) {
                $this->files[$thumbnail->fileID]->addThumbnail($thumbnail);
            }
        }

        return parent::value($value);
    }

    #[\Override]
    public function validate()
    {
        if ($this->isRequired() && $this->files === []) {
            $this->addValidationError(new FormFieldValidationError('empty'));
        }

        $fileProcessor = $this->getFileProcessor();

        if (\count($this->files) > $fileProcessor->getMaximumCount($this->context)) {
            $this->addValidationError(
                new FormFieldValidationError(
                    'maximumFiles',
                    'wcf.upload.error.maximumCountReached',
                    [
                        'maximumCount' => $fileProcessor->getMaximumCount($this->context),
                        'count' => \count($this->files),
                    ]
                )
            );
        }

        foreach ($this->files as $file) {
            if (!FileProcessor::getInstance()->canAdopt($fileProcessor, $file, $this->context)) {
                $this->addValidationError(
                    new FormFieldValidationError(
                        'adopt',
                        'wcf.upload.error.adopt',
                        ['filename' => $file->filename]
                    )
                );
            }
        }

        parent::validate();
    }

    /**
     * Adds an action button to the file processor.
     * If the button is clicked, the event `fileProcessorCustomAction` will be triggered.
     */
    public function addActionButton(
        string $actionName,
        string $title,
        string $template,
        string $application = 'wcf',
        ?IFontAwesomeIcon $icon = null
    ): self {
        $this->actionButtons[] = [
            'actionName' => $actionName,
            'title' => $title,
            'icon' => $icon,
            'application' => $application,
            'template' => $template,
        ];

        return $this;
    }

    /**
     * Returns the context for the file processor.
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Sets the context for the file processor.
     */
    public function context(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Returns whether the big preview is shown for images.
     */
    public function isBigPreview(): bool
    {
        return $this->bigPreview;
    }

    /**
     * Sets whether the big preview is shown for images.
     */
    public function bigPreview(bool $bigPreview = true): self
    {
        if (
            $bigPreview
            && \array_diff(
                $this->getFileProcessor()->getAllowedFileExtensions($this->context),
                ImageUtil::IMAGE_EXTENSIONS
            ) !== []
        ) {
            throw new \InvalidArgumentException(
                "The big preview is only supported for images for the field '{$this->getId()}'."
            );
        }

        if ($bigPreview && !$this->singleFileUpload) {
            throw new \InvalidArgumentException(
                "The big preview is only supported for single file uploads for the field '{$this->getId()}'."
            );
        }

        $this->bigPreview = $bigPreview;

        return $this;
    }
}
