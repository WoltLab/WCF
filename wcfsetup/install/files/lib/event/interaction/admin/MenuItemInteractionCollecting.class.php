<?php

namespace wcf\event\interaction\admin;

use wcf\event\IPsr14Event;
use wcf\system\interaction\admin\MenuItemInteractions;

/**
 * Indicates that the provider for menu item interactions has been initialized.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class MenuItemInteractionCollecting implements IPsr14Event
{
    public function __construct(public readonly MenuItemInteractions $provider) {}
}
