<?php

namespace wcf\system\form\option;

use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\wysiwyg\WysiwygFormField;
use wcf\system\form\option\formatter\IFormOptionFormatter;
use wcf\system\form\option\formatter\WysiwygFormatter;
use wcf\system\form\option\formatter\WysiwygPlainTextFormatter;

/**
 * Implementation of a form option using the WYSIWYG editor.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class WysiwygFormOption extends AbstractFormOption
{
    private string $objectType;

    private int $objectID;

    #[\Override]
    public function getId(): string
    {
        return 'wysiwyg';
    }

    #[\Override]
    public function getFormField(string $id, array $configuration = []): AbstractFormField
    {
        if (!isset($this->objectType)) {
            throw new \RuntimeException("The WYSIWYG context has not been set.");
        }

        return WysiwygFormField::create($id)
            ->objectType($this->objectType);
    }

    #[\Override]
    public function getConfigurationFormFields(): array
    {
        return ['required'];
    }

    #[\Override]
    public function getFormatter(): IFormOptionFormatter
    {
        if (!isset($this->objectType)) {
            throw new \RuntimeException("The WYSIWYG context has not been set.");
        }

        return new WysiwygFormatter($this->objectType, $this->objectID);
    }

    #[\Override]
    public function getPlainTextFormatter(): IFormOptionFormatter
    {
        if (!isset($this->objectType)) {
            throw new \RuntimeException("The WYSIWYG context has not been set.");
        }

        return new WysiwygPlainTextFormatter($this->objectType, $this->objectID);
    }

    #[\Override]
    public function getFilterFormField(string $id, array $configuration = []): AbstractFormField
    {
        throw new \BadMethodCallException("WysiwygFormOption does not support filtering.");
    }

    #[\Override]
    public function isFilterable(): bool
    {
        return false;
    }

    /**
     * Sets the context for the HTML processors.
     */
    public function setContext(string $objectType, int $objectID): void
    {
        $this->objectType = $objectType;
        $this->objectID = $objectID;
    }
}
