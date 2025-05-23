<?php

namespace wcf\system\form\builder\field;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * TODO The time/date value must be saved with the system timezone.
 */
final class DateConditionFormField extends AbstractConditionFormField implements
    IAttributeFormField,
    IAutoFocusFormField,
    ICssClassFormField,
    INullableFormField
{
    use TAttributeFormField;
    use TAutoFocusFormField;
    use TCssClassFormField;
    use TNullableFormField;

    public const DATE_FORMAT = 'Y-m-d';
    public const TIME_FORMAT = 'Y-m-d\TH:i:sP';

    /**
     * is `true` if not only the date, but also the time can be set
     */
    protected bool $supportsTime = false;
    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_dateConditionFormField';

    #[\Override]
    public function validate()
    {
        // TODO validate date value
        parent::validate();
    }

    /**
     * Sets if not only the date, but also the time can be set.
     */
    public function supportTime(bool $supportsTime = true): self
    {
        if ($this->value !== null) {
            throw new \BadFunctionCallException(
                "After a value has been set, time support cannot be changed for field '{$this->getId()}'."
            );
        }

        $this->supportsTime = $supportsTime;

        return $this;
    }

    /**
     * Returns `true` if not only the date, but also the time can be set, and
     * returns `false` otherwise.
     *
     * By default, the time cannot be set.
     */
    public function supportsTime(): bool
    {
        return $this->supportsTime;
    }
}
