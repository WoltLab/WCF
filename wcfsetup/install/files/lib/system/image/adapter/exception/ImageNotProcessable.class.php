<?php

namespace wcf\system\image\adapter\exception;

/**
 * The target image cannot be processed by the image adapter for an unspecified
 * reason. This could be the cause of missing codecs or damaged files.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class ImageNotProcessable extends \Exception
{
    public function __construct(string $filename, ?\Throwable $previous = null)
    {
        parent::__construct("The image '{$filename}' cannot be processed.", previous: $previous);
    }
}
