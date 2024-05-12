<?php

namespace wcf\event\package;

use wcf\event\IPsr14Event;

/**
 * Indicates that the there have been changes to the
 * package list. These changes include the installation,
 * removal or update of existing packages.
 *
 * The event is fired at the end of the overall process
 * and not for each package that has been modified.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class PackageListChanged extends \wcf\system\package\event\PackageListChanged implements IPsr14Event
{
}
