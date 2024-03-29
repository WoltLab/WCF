<?php

namespace wcf\data;

/**
 * Abstract class for all cached data holder objects.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IEditableCachedObject extends IEditableObject
{
    /**
     * Resets the cache of this object type.
     */
    public static function resetCache();
}
