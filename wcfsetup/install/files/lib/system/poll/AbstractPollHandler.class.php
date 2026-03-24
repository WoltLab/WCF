<?php

namespace wcf\system\poll;

use wcf\system\SingletonFactory;

/**
 * Basic implementation for poll handlers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractPollHandler extends SingletonFactory implements IPollHandler
{
    #[\Override]
    public function canStartPublicPoll()
    {
        return true;
    }

    #[\Override]
    public function canVote()
    {
        return true;
    }
}
