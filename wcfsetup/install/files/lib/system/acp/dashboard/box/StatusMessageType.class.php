<?php

namespace wcf\system\acp\dashboard\box;

/**
 * Represents the type of a status message.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
enum StatusMessageType
{
    case Warning;
    case Error;
    case Info;

    public function getClassName(): string
    {
        return match ($this) {
            self::Error => 'error',
            self::Info => 'info',
            self::Warning => 'warning',
        };
    }

    public function compare(StatusMessageType $type): int
    {
        if ($this === $type) {
            return 0;
        }

        if (
            $this === self::Error
            || ($this === self::Warning && $type === self::Info)
        ) {
            return -1;
        }

        if (
            $type === self::Error
            || ($type === self::Warning && $this === self::Info)
        ) {
            return 1;
        }
    }
}
