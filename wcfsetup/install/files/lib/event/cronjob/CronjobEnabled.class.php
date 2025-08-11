<?php

namespace wcf\event\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\event\IPsr14Event;

/**
 * Indicates that a cronjob has been enabled.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class CronjobEnabled implements IPsr14Event
{
    public function __construct(public readonly Cronjob $cronjob) {}
}
