<?php

namespace wcf\system\option;

use wcf\data\option\Option;

/**
 * Provides a default implementation for object types.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractOptionType implements IOptionType
{
    /**
     * internationalization support
     * @var bool
     */
    protected $supportI18n = false;

    #[\Override]
    public function validate(Option $option, mixed $newValue) {}

    #[\Override]
    public function getData(Option $option, mixed $newValue)
    {
        return $newValue;
    }

    #[\Override]
    public function getCSSClassName()
    {
        return '';
    }

    #[\Override]
    public function supportI18n()
    {
        return $this->supportI18n;
    }

    #[\Override]
    public function compare(mixed $value1, mixed $value2)
    {
        return 0;
    }

    #[\Override]
    public function hideLabelInSearch()
    {
        return false;
    }

    #[\Override]
    public function getDisabledOptionNames(mixed $value, string $enableOptions)
    {
        return [];
    }
}
