<?php

namespace wcf\system\file\processor\exception;

/**
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class DamagedImage extends \Exception
{
    public function __construct(public readonly int $fileID)
    {
        parent::__construct(
            \sprintf(
                "The file '%d' is a damaged image.",
                $this->fileID,
            ),
        );
    }
}
