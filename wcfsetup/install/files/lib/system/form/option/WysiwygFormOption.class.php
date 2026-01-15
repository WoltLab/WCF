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
    #[\Override]
    public function getId(): string
    {
        return 'wysiwyg';
    }

    #[\Override]
    public function getFormField(string $id, array $configuration = []): AbstractFormField
    {
        return WysiwygFormField::create($id)
            ->objectType('com.woltlab.wcf.genericFormOption');
    }

    #[\Override]
    public function getConfigurationFormFields(): array
    {
        return ['required'];
    }

    #[\Override]
    public function getFormatter(): IFormOptionFormatter
    {
        return new WysiwygFormatter();
    }

    #[\Override]
    public function getPlainTextFormatter(): IFormOptionFormatter
    {
        return new WysiwygPlainTextFormatter();
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
}
