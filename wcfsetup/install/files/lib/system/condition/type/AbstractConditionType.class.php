<?php

namespace wcf\system\condition\type;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 *
 * @template TFilter
 * @implements IConditionType<TFilter>
 */
abstract class AbstractConditionType implements IConditionType
{
    protected mixed $filter;

    /**
     * @inheritDoc
     */
    public function setFilter(mixed $filter): void
    {
        $this->filter = $filter;
    }
}
