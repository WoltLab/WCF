<?php

namespace wcf\system\cache\event;

use wcf\system\event\IEvent;

/**
 * Indicates that a full cache clear was performed.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Cache\Event
 * @since   6.0
 */
final class CacheCleared implements IEvent
{
    public function __construct()
    {
    }
}
