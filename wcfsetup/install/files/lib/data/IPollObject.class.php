<?php

namespace wcf\data;

/**
 * Default interface for DatabaseObjects with poll support.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data
 */
interface IPollObject
{
    /**
     * Returns true if user can vote in polls.
     *
     * @return  bool
     */
    public function canVote();
}
