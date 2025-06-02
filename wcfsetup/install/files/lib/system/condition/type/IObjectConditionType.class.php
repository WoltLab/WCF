<?php

namespace wcf\system\condition\type;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @template TObject of object
 * @template TFilter
 * @extends  IConditionType<TFilter>
 */
interface IObjectConditionType extends IConditionType
{
    /**
     * @param TObject $object
     */
    public function matches(object $object): bool;
}
