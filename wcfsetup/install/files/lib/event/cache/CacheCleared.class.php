<?php

namespace wcf\event\cache;

use wcf\event\IPsr14Event;

/**
 * Indicates that a full cache clear was performed.
 *
 * @author      Tim Duesterhus
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class CacheCleared extends \wcf\system\cache\event\CacheCleared implements IPsr14Event
{
    public function __construct()
    {
    }
}
