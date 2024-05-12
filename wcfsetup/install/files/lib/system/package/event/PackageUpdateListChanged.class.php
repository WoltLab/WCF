<?php

namespace wcf\system\package\event;

use wcf\data\package\update\server\PackageUpdateServer;
use wcf\system\event\IEvent;

/**
 * Indicates that the there have been changes to the
 * package update list.
 *
 * @author Florian Gail
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @deprecated 6.1 use `wcf\event\package\PackageUpdateListChanged`
 */
class PackageUpdateListChanged implements IEvent
{
    public function __construct(
        public readonly PackageUpdateServer $updateServer,
    ) {
    }
}
