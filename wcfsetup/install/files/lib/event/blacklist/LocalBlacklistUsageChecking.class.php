<?php

namespace wcf\event\blacklist;

use wcf\event\IInterruptableEvent;
use wcf\event\TInterruptableEvent;

/**
 * Indicates that Core is about to use or update its local copy of the Stop Forum
 * Spam data. Calling `preventDefault()` means that another component handles the
 * Stop Forum Spam checks, so Core neither uses nor updates its local copy.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class LocalBlacklistUsageChecking implements IInterruptableEvent
{
    use TInterruptableEvent;
}
