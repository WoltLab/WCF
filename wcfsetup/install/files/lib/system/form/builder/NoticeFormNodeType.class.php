<?php

namespace wcf\system\form\builder;

/**
 * Defines the available types for the notice form node.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
enum NoticeFormNodeType
{
    case Info;
    case Success;
    case Warning;
    case Error;

    public function toString(): string
    {
        return match ($this) {
            self::Info => 'info',
            self::Success => 'success',
            self::Warning => 'warning',
            self::Error => 'error',
        };
    }
}
