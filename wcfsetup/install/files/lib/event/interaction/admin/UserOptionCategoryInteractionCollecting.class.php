<?php

namespace wcf\event\interaction\admin;

use wcf\event\IPsr14Event;
use wcf\system\interaction\admin\UserOptionCategoryInteractions;

/**
 * Indicates that the provider for user option category interactions is collecting interactions.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserOptionCategoryInteractionCollecting implements IPsr14Event
{
    public function __construct(public readonly UserOptionCategoryInteractions $provider) {}
}
