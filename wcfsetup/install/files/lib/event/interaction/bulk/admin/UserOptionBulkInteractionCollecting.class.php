<?php

namespace wcf\event\interaction\bulk\admin;

use wcf\event\IPsr14Event;
use wcf\system\interaction\bulk\admin\UserOptionBulkInteractions;

/**
 * Indicates that the provider for user option bulk interactions is collecting interactions.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserOptionBulkInteractionCollecting implements IPsr14Event
{
    public function __construct(public readonly UserOptionBulkInteractions $provider) {}
}
