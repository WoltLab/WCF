<?php

namespace wcf\system\form\builder\field;

use wcf\system\file\processor\FileProcessor;
use wcf\system\file\processor\IFileProcessor;
use wcf\system\form\builder\TObjectTypeFormNode;

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

    #[\Override]
    public function readValue()
    {
        \wcfDebug($this->getDocument()->getRequestData());
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $this->context = $this->getDocument()->getRequestData($this->getPrefixedId());
        }
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
        ];
    }

    public function getFileProcessor(): IFileProcessor
    {
        return $this->getObjectType()->getProcessor();
    }

    #[\Override]
    public function getObjectTypeDefinition()
    {
        return 'com.woltlab.wcf.file';
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
