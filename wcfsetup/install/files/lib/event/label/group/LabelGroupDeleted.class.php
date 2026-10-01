<?php

namespace wcf\event\label\group;

use wcf\data\label\group\LabelGroup;
use wcf\event\IPsr14Event;

/**
 * Indicates that a label group has been deleted.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class LabelGroupDeleted implements IPsr14Event
{
    public function __construct(
        public readonly LabelGroup $group,
    ) {}
}
