<?php

namespace wcf\system\condition;

use wcf\data\object\type\AbstractObjectTypeProcessor;

/**
 * Abstract implementation of a condition.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method ?mixed[] getData()
 */
abstract class AbstractCondition extends AbstractObjectTypeProcessor implements ICondition
{
    #[\Override]
    public function reset()
    {
        // does nothing
    }

    #[\Override]
    public function validate()
    {
        // does nothing
    }
}
