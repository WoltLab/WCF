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

    private function priority(): int
    {
        return match ($this) {
            self::Error => 2,
            self::Warning => 1,
            self::Info => 0,
        };
    }

    public static function compare(self $a, self $b): int
    {
        return $b->priority() <=> $a->priority();
    }
}
