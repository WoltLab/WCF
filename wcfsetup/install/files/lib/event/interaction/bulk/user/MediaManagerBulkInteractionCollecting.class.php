<?php

namespace wcf\event\interaction\bulk\user;

use wcf\event\IPsr14Event;
use wcf\system\interaction\bulk\user\MediaManagerBulkInteractions;

/**
 * Indicates that the provider for the bulk interactions of the media manager is collecting interactions.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class MediaManagerBulkInteractionCollecting implements IPsr14Event
{
    public function __construct(public readonly MediaManagerBulkInteractions $provider) {}
}
