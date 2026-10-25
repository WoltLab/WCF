<?php

namespace wcf\system\poll;

use wcf\system\SingletonFactory;

/**
 * Basic implementation for poll handlers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use standalone polls that are embedded through the `[poll]` BBCode instead.
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
