<?php

namespace wcf\system\form\builder\field;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class IntegerConditionFormField extends AbstractConditionFormField implements
    IAttributeFormField,
    IAutoFocusFormField,
    ICssClassFormField,
    INullableFormField,
    IPlaceholderFormField,
    IAutoCompleteFormField,
    ISuffixedFormField
{
    use TAttributeFormField {
        getReservedFieldAttributes as private defaultGetReservedFieldAttributes;
    }
    use TAutoFocusFormField;
    use TCssClassFormField;
    use TNullableFormField;
    use TPlaceholderFormField;
    use TAutoCompleteFormField;
    use TInputModeFormField;
    use TSuffixedFormField;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_numericConditionFormField';

    /**
     * step value for the input element
     */
    protected int $step = 1;

    public function __construct()
    {
        $this->addFieldClass('short');
    }

    /**
     * @return string[]
     */
    protected static function getReservedFieldAttributes(): array
    {
        return \array_merge(
            self::defaultGetReservedFieldAttributes(),
            [
                'step',
            ]
        );
    }

    #[\Override]
    public function validate()
    {
        parent::validate();
        // TODO

    }

    public function step(int $step): self
    {
        $this->step = $step;

        return $this;
    }

    public function getStep(): int
    {
        return $this->step;
    }

    #[\Override]
    public function getFieldValue(): int
    {
        return \intval(parent::getFieldValue());
    }

    /**
     * @return string[]
     */
    protected function getValidInputModes(): array
    {
        return ['numeric'];
    }
}
