<?php

namespace wcf\system\file\processor\exception;

/**
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class DuplicateFileProcessor extends \Exception
{
    public function __construct(string $typeName)
    {
        parent::__construct(
            \sprintf("The file processor '%s' has already been registered", $typeName),
        );
    }
}
