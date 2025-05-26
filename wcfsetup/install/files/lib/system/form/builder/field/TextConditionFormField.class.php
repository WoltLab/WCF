<?php

namespace wcf\system\form\builder\field;

use wcf\util\StringUtil;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class TextConditionFormField extends AbstractConditionFormField implements
    IAttributeFormField,
    IAutoFocusFormField,
    ICssClassFormField,
    INullableFormField,
    IPlaceholderFormField,
    IAutoCompleteFormField
{
    use TAttributeFormField;
    use TAutoFocusFormField;
    use TCssClassFormField;
    use TNullableFormField;
    use TPlaceholderFormField;
    use TAutoCompleteFormField;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_textConditionFormField';

    public function __construct()
    {
        $this->addFieldClass('medium');
    }

    #[\Override]
    public function hasFieldValue(): bool
    {
        return parent::hasFieldValue() && \mb_strlen($this->getFieldValue()) > 0;
    }

    #[\Override]
    public function getFieldValue(): string
    {
        return StringUtil::trim(parent::getFieldValue());
    }
}
