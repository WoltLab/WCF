<?php

namespace wcf\data\acp\session\log;

use wcf\data\DatabaseObject;
use wcf\system\WCF;
use wcf\util\UserUtil;

/**
 * Represents a acp session log entry.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $sessionLogID       unique id of the acp session log entry
 * @property-read   string $sessionID      id of the acp session the acp session log entry belongs to
 * @property-read   int|null $userID         id of the user who has caused the acp session log entry or `null`
 * @property-read   string $ipAddress      ip address of the user who has caused the acp session access log entry
 * @property-read   string $hostname       name of the internet host corresponding to the user's IP address
 * @property-read   string $userAgent      user agent of the user who has caused the acp session access log entry
 * @property-read   int $time           timestamp at which the acp session log entry has been created
 * @property-read   int $lastActivityTime   timestamp at which the associated session has been active for the last time
 * @property-read   string|null $active         has the corresponding acp session id as the value if the session is still active, otherwise `null`
 */
class ACPSessionLog extends DatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'sessionLogID';

    /** @noinspection PhpMissingParentConstructorInspection */

    /**
     * @inheritDoc
     */
    public function __construct($id, ?array $row = null, ?DatabaseObject $object = null)
    {
        if ($id !== null) {
            $sql = "SELECT      acp_session_log.*, user_table.username, 0 AS active
                    FROM        wcf1_acp_session_log acp_session_log
                    LEFT JOIN   wcf1_user user_table
                    ON          user_table.userID = acp_session_log.userID
                    WHERE       acp_session_log.sessionLogID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$id]);
            $row = $statement->fetchArray();
        } elseif ($object !== null) {
            $row = $object->data;
        }

        $this->handleData($row);
    }

    /**
     * @deprecated 5.4 - This method always returns false.
     */
    public function isActive()
    {
        return false;
    }

    /**
     * @deprecated 5.4 - This method always returns false.
     */
    public function isActiveUserSession()
    {
        return false;
    }

    /**
     * Returns the ip address and attempts to convert into IPv4.
     *
     * @return  string
     */
    public function getIpAddress()
    {
        return UserUtil::convertIPv6To4($this->ipAddress);
    }
}
