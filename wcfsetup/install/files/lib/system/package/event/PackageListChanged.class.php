<?php

namespace wcf\system\package\event;

use wcf\system\event\IEvent;

/**
 * Indicates that the there have been changes to the
 * package list. These changes include the installation,
 * removal or update of existing packages.
 *
 * The event is fired at the end of the overall process
 * and not for each package that has been modified.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @deprecated 6.1 use `wcf\event\package\PackageListChanged` instead
 */
class PackageListChanged implements IEvent
{
}
