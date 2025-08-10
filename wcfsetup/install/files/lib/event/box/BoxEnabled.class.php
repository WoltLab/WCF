<?php

namespace wcf\event\box;

use wcf\data\box\Box;
use wcf\event\IPsr14Event;

/**
 * Indicates that a box has been enabled.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class BoxEnabled implements IPsr14Event
{
    public function __construct(public readonly Box $box) {}
}
