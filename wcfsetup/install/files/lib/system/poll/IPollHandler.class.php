<?php

namespace wcf\system\poll;

use wcf\data\poll\Poll;

/**
 * Provides methods to create and manage polls.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IPollHandler
{
    /**
     * Returns true if current user may start a public poll.
     *
     * @return  bool
     */
    public function canStartPublicPoll();

    /**
     * Returns true if current user may vote.
     *
     * @return  bool
     */
    public function canVote();

    /**
     * Returns related object for given poll object.
     *
     * @param Poll $poll
     */
    public function getRelatedObject(Poll $poll);
}
