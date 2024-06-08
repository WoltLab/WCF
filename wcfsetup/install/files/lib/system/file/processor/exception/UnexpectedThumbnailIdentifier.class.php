<?php

namespace wcf\system\file\processor\exception;

/**
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class UnexpectedThumbnailIdentifier extends \Exception
{
    public function __construct(string $identifier)
    {
        parent::__construct(
            \sprintf(
                "The thumbnail identifier '%s' is unsupported for this type.",
                $identifier,
            ),
        );
    }
}
