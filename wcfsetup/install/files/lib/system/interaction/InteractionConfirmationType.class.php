<?php

namespace wcf\system\interaction;

/**
 * Represents a confirmation type used in interactions.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
enum InteractionConfirmationType
{
    case None;
    case SoftDelete;
    case SoftDeleteWithReason;
    case Restore;
    case Delete;
    case Custom;

    public function toString(): string
    {
        return match ($this) {
            self::None => 'None',
            self::SoftDelete => 'SoftDelete',
            self::SoftDeleteWithReason => 'SoftDeleteWithReason',
            self::Restore => 'Restore',
            self::Delete => 'Delete',
            self::Custom => 'Custom',
        };
    }
}
