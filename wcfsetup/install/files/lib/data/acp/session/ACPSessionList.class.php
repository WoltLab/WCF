<?php

namespace wcf\data\acp\session;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of ACP sessions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Acp\Session
 *
 * @method  ACPSession      current()
 * @method  ACPSession[]        getObjects()
 * @method  ACPSession|null     search($objectID)
 * @property    ACPSession[] $objects
 * @deprecated  5.4 Distinct ACP sessions have been removed. This class is preserved due to its use in legacy sessions.
 */
class ACPSessionList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = ACPSession::class;
}
