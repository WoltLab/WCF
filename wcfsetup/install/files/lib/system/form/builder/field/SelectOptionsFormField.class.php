<?php

namespace wcf\system\form\builder\field;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\ICssClassFormField;
use wcf\system\form\builder\field\IImmutableFormField;
use wcf\system\form\builder\field\TCssClassFormField;
use wcf\system\form\builder\field\TImmutableFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\language\LanguageFactory;

/**
 * Form field that allows to configure the options for select-type form fields.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class SelectOptionsFormField extends AbstractFormField implements
    ICssClassFormField,
    IImmutableFormField
{
    use TCssClassFormField;
    use TImmutableFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_selectOptionsFormField';

    #[\Override]
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());

            if (\is_string($value)) {
                $this->value = $value !== '' ? $value : null;
            }
        }

        return $this;
    }

    #[\Override]
    public function getHtmlVariables()
    {
        return [
            'availableLanguages' => \array_map(
                static fn($language) => $language->__toString(),
                LanguageFactory::getInstance()->getLanguages()
            ),
        ];
    }

    #[\Override]
    public function validate()
    {
        try {
            $mapper = (new MapperBuilder())->mapper();
            $mapper->map(
                <<<'EOT'
                    list<array{
                        key: string,
                        value: array<int, string>,
                    }>
                    EOT,
                Source::json($this->getValue())
            );
        } catch (MappingError) {
            $this->addValidationError(new FormFieldValidationError('empty'));
        }

        parent::validate();
    }
}
