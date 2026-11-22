<?php

namespace wcf\event\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\event\IPsr14Event;

/**
 * Indicates that a cronjob is about to be deleted.
 * Fired before any data is removed, allowing the cleanup of linked data.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CronjobDeleting implements IPsr14Event
{
    public function __construct(
        public readonly Cronjob $cronjob,
    ) {}
}
