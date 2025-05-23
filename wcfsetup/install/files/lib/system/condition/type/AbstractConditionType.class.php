<?php

namespace wcf\system\condition\type;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
abstract class AbstractConditionType implements IConditionType
{
    protected float|int|string $filter;

    /**
     * @inheritDoc
     */
    public function setFilter(float|int|string $filter): void
    {
        $this->filter = $filter;
    }
}
