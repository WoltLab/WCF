<?php

namespace wcf\event\label\group;

use wcf\data\label\group\LabelGroup;
use wcf\data\label\group\LabelGroupBuilder;
use wcf\event\IPsr14Event;

/**
 * Indicates that a label group has been updated.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class LabelGroupUpdated implements IPsr14Event
{
    public function __construct(
        public readonly LabelGroup $group,
        public readonly LabelGroupBuilder $builder,
    ) {}
}
