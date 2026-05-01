<?php

namespace wcf\event\style;

use wcf\event\IPsr14Event;
use wcf\system\style\IFontAwesomeIcon;

/**
 * Resolves a stored icon string to a renderable icon instance.
 *
 * @author      Sascha Greuel
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class StoredIconResolving implements IPsr14Event
{
    public ?IFontAwesomeIcon $icon = null;

    public function __construct(
        public readonly string $iconData
    ) {
    }
}
