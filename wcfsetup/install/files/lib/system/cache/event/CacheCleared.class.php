<?php

namespace wcf\system\cache\event;

use wcf\system\event\IEvent;

/**
 * Indicates that a full cache clear was performed.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 * @deprecated 6.1 use `wcf\event\cache\CacheCleared` instead
 */
class CacheCleared implements IEvent
{
    public function __construct()
    {
    }
}
