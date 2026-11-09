<?php

namespace wcf\system\label\object;

/**
 * Marks a label object handler whose objects support more than one label per
 * label group. The number of labels is limited by the `maxSelection` of each
 * label group, label object handlers without this interface always allow only
 * a single label per group.
 *
 * Implementations must accept `groupID => labelID[]` for groups that allow
 * multiple labels.
 *
 * @author  Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.3
 */
interface IMultipleLabelsObjectHandler extends ILabelObjectHandler {}
