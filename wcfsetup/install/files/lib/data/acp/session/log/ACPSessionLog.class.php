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
 * @property-read   int     $sessionLogID       unique id of the acp session log entry
 * @property-read   string  $sessionID          id of the acp session the acp session log entry belongs to
 * @property-read   ?int    $userID             id of the user who has caused the acp session log entry or `null`
 * @property-read   string  $ipAddress          ip address of the user who has caused the acp session access log entry
 * @property-read   string  $userAgent          user agent of the user who has caused the acp session access log entry
 * @property-read   int     $time               timestamp at which the acp session log entry has been created
 * @property-read   int     $lastActivityTime   timestamp at which the associated session has been active for the last time
 */
class ACPSessionLog extends DatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'sessionLogID';

    public function __construct(null|string|int $id, ?array $row = null, ?DatabaseObject $object = null)
    {
        if ($id !== null) {
            $sql = "SELECT      acp_session_log.*, user_table.username
                    FROM        wcf1_acp_session_log acp_session_log
                    LEFT JOIN   wcf1_user user_table
                    ON          user_table.userID = acp_session_log.userID
                    WHERE       acp_session_log.sessionLogID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$id]);
            $row = $statement->fetchArray();

            if ($row === false) {
                $row = [];
            }
        }

        parent::__construct(null, $row, $id !== null ? null : $object);
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

    /**
     * @since 6.3
     */
    public static function getActiveLogBySessionID(string $sessionID): ?self
    {
        $sql = "SELECT  *
                FROM    wcf1_acp_session_log
                WHERE   sessionID = ?
                    AND lastActivityTime > ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $sessionID,
            (\TIME_NOW - 15 * 60),
        ]);

        return $statement->fetchSingleObject(self::class);
    }
}
