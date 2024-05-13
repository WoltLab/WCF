<?php

namespace wcf\event\package;

use wcf\data\package\update\server\PackageUpdateServer;
use wcf\event\IPsr14Event;

/**
 * Indicates that the there have been changes to the
 * package update list.
 *
 * @author      Florian Gail
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class PackageUpdateListChanged extends \wcf\system\package\event\PackageUpdateListChanged implements IPsr14Event
{
    public function __construct(
        public readonly PackageUpdateServer $updateServer,
    ) {
    }
}
