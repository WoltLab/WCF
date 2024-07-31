<?php

namespace wcf\system\image\adapter\exception;

/**
 * The target image does not exist or cannot be read.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class ImageNotReadable extends \Exception
{
    #[\Override]
    public function __construct(string $filename, ?\Throwable $previous = null)
    {
        parent::__construct("The image '{$filename}' does not exist or cannot be accessed.", previous: $previous);
    }
}
