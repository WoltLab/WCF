<?php

namespace wcf\system\condition\type;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @template T of object
 */
interface IObjectConditionType extends IConditionType
{
    /**
     * Returns `true` if the given object matches the filter, `false` otherwise.
     *
     * @param T $object
     */
    public function match(object $object, float|int|string $filter): bool;
}
