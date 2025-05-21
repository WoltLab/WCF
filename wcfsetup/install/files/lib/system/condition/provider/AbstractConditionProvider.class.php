<?php

namespace wcf\system\condition\provider;

use wcf\system\condition\type\IConditionType;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 *
 * @template T of IConditionType
 */
abstract class AbstractConditionProvider
{
    /**
     * @var array<string, T>
     */
    protected array $conditionTypes = [];

    /**
     * @param T $conditionType
     */
    public function addCondition(IConditionType $conditionType): void
    {
        $this->conditionTypes[$conditionType->getIdentifier()] = $conditionType;
    }

    /**
     * @param T[] $conditionTypes
     */
    public function addConditions(array $conditionTypes): void
    {
        foreach ($conditionTypes as $conditionType) {
            $this->addCondition($conditionType);
        }
    }
}
