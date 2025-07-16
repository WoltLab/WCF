<?php

namespace wcf\system\condition\type;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @template TFilter
 * @extends  IConditionType<TFilter>
 */
interface IContextualConditionType extends IConditionType
{
    /**
     * Returns `true` if the condition matches the global context (e.g., the active user via `WCF::getUser()`).
     */
    public function matches(): bool;
}
