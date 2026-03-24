<?php

namespace wcf\system\html\metacode\converter;

/**
 * Default implementation for metacode converters.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractMetacodeConverter implements IMetacodeConverter
{
    #[\Override]
    public function validateAttributes(array $attributes)
    {
        return true;
    }
}
