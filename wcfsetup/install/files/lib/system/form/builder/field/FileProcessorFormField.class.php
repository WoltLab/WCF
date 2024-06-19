<?php

namespace wcf\system\form\builder\field;

use wcf\data\file\File;
use wcf\data\file\FileList;
use wcf\data\file\thumbnail\FileThumbnailList;
use wcf\system\file\processor\FileProcessor;
use wcf\system\file\processor\IFileProcessor;
use wcf\system\form\builder\TObjectTypeFormNode;
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

    private array $context = [];

    /**
     * @var File[]
     */
    private array $files = [];
    private bool $singleFileUpload = false;

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
            'imageOnly' => \array_diff(
                $this->getFileProcessor()->getAllowedFileExtensions($this->context),
                ImageUtil::IMAGE_EXTENSIONS
            ) === []
        ];
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
    public function setSingleFileUpload(bool $singleFileUpload): self
    {
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
                $this->files = [$file];
                $fileIDs[] = $value;
            }
        } else {
            if (!\is_array($value)) {
                $value = [$value];
            }

            $fileList = new FileList();
            $fileList->setObjectIDs($value);
            $fileList->readObjects();
            $this->files = $fileList->getObjects();

            $fileIDs = $fileList->getObjectIDs();
        }

        $thumbnailList = new FileThumbnailList();
        $thumbnailList->getConditionBuilder()->add("fileID IN (?)", [$fileIDs]);
        $thumbnailList->readObjects();
        foreach ($thumbnailList as $thumbnail) {
            $this->files[$thumbnail->fileID]->addThumbnail($thumbnail);
        }

        return parent::value($value);
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
}
