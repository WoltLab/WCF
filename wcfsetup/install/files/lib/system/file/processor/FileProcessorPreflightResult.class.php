<?php

namespace wcf\system\file\processor;

/**
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
enum FileProcessorPreflightResult
{
    case FileExtensionNotPermitted;
    case FileSizeTooLarge;
    case InsufficientPermissions;
    case InvalidContext;
    case Passed;

    public function ok(): bool
    {
        return match ($this) {
            self::Passed => true,
            default => false,
        };
    }

    public function toString(): string
    {
        return match ($this) {
            self::FileExtensionNotPermitted => 'fileExtensionNotPermitted',
            self::FileSizeTooLarge => 'fileSizeTooLarge',
            self::InsufficientPermissions => 'insufficientPermissions',
            self::InvalidContext => 'invalidContext',
            self::Passed => 'passed',
        };
    }
}
