<?php

namespace wcf\system\package\license\exception;

use CuyZ\Valinor\Mapper\MappingError;

/**
 * The license data does not match the expectations of the license API.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class ParsingFailed extends \Exception
{
    public function __construct(MappingError $previous)
    {
        parent::__construct('The provided license data cannot be parsed.', 0, $previous);
    }
}
