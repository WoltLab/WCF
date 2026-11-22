<?php

namespace wcf\event\label\group;

use wcf\data\label\group\LabelGroup;
use wcf\event\IPsr14Event;

/**
 * Indicates that a label group is about to be deleted.
 * Fired before any data is removed, allowing the cleanup of linked data.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class LabelGroupDeleting implements IPsr14Event
{
    public function __construct(
        public readonly LabelGroup $group,
    ) {}
}
