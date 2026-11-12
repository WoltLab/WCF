<?php

namespace wcf\data\blacklist\entry;

use wcf\data\DatabaseObject;
use wcf\event\blacklist\LocalBlacklistUsageChecking;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\WCF;
use wcf\util\IpAddress;

/**
 * Represents a blacklist entry.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read string $type One of 'email', 'ipv4', 'ipv6' or 'username'
 * @property-read string $hash SHA256 hash of the original value
 * @property-read int $lastSeen Timestamp of the last report, derivation is up to 24hrs
 * @property-read int $occurrences Number of times this value was reported, capped at 32,767
 * @since 5.2
 */
class BlacklistEntry extends DatabaseObject
{
    private static ?bool $isEnabled = null;

    /**
     * Returns true if the built-in Stop Forum Spam checks are active, which also
     * covers updating the local copy of the data.
     *
     * @since 6.2
     */
    public static function isEnabled(): bool
    {
        if (self::$isEnabled === null) {
            if (\BLACKLIST_SFS_ENABLE === 0) {
                self::$isEnabled = false;
            } else {
                $event = new LocalBlacklistUsageChecking();
                EventHandler::getInstance()->fire($event);

                self::$isEnabled = !$event->defaultPrevented();
            }
        }

        return self::$isEnabled;
    }

    /**
     * @param string $username
     * @param string $email
     * @param string $ipAddress
     * @return string[]
     */
    public static function getMatches($username, $email, $ipAddress)
    {
        if (BLACKLIST_SFS_USERNAME === 'skip' && BLACKLIST_SFS_EMAIL_ADDRESS === 'skip' && BLACKLIST_SFS_IP_ADDRESS === 'skip') {
            return [];
        }

        $conditions = new PreparedStatementConditionBuilder(true, 'OR');
        if (BLACKLIST_SFS_USERNAME && $username !== '') {
            $conditions->add('(type = ? AND hash = ?)', ['username', self::getHash($username)]);
        }
        if (BLACKLIST_SFS_EMAIL_ADDRESS) {
            $conditions->add('(type = ? AND hash = ?)', ['email', self::getHash($email)]);
        }
        if (BLACKLIST_SFS_IP_ADDRESS) {
            if ($ipAddress) {
                $ipAddress = new IpAddress($ipAddress);
                if (($ipv4 = $ipAddress->asV4()) !== null) {
                    $conditions->add('(type = ? AND hash = ?)', ['ipv4', self::getHash($ipv4->getIpAddress())]);
                } else {
                    // StopForumSpam uses the first two to four segments of an IPv6 address.
                    $ipv6TwoParts = $ipAddress->toMasked(32, 32)->getIpAddress();
                    $ipv6ThreeParts = $ipAddress->toMasked(32, 48)->getIpAddress();
                    $ipv6FourParts = $ipAddress->toMasked(32, 64)->getIpAddress();

                    $conditions->add(
                        '(type = ? AND hash IN (?))',
                        [
                            'ipv6',
                            [
                                self::getHash($ipv6TwoParts),
                                self::getHash($ipv6ThreeParts),
                                self::getHash($ipv6FourParts),
                            ],
                        ]
                    );
                }
            }
        }

        $sql = "SELECT  type, occurrences
                FROM    wcf1_blacklist_entry
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $matches = [];
        while ($row = $statement->fetchArray()) {
            if (self::isMatch($row['type'], $row['occurrences'])) {
                $matches[] = ($row['type'] === 'ipv4' || $row['type'] === 'ipv6') ? 'ip' : $row['type'];
            }
        }

        return $matches;
    }

    /**
     * @param string $string
     * @return string
     */
    protected static function getHash($string)
    {
        return \hash('sha256', $string, true);
    }

    /**
     * @param string $type
     * @param int $occurrences
     * @return bool
     */
    protected static function isMatch($type, $occurrences)
    {
        $setting = [
            'email' => BLACKLIST_SFS_EMAIL_ADDRESS,
            'ipv4' => BLACKLIST_SFS_IP_ADDRESS,
            'ipv6' => BLACKLIST_SFS_IP_ADDRESS,
            'username' => BLACKLIST_SFS_USERNAME,
        ][$type];

        switch ($setting) {
            case '90percentile':
                if ($occurrences >= self::get90Percentile($type)) {
                    return true;
                }
                break;

            case 'moreThanOnce':
                return $occurrences > 1;

            case 'simpleMatch':
                // We could just return `true`, but this makes it much more clearer.
                return $occurrences > 0;
        }

        return false;
    }

    /**
     * @param string $type
     * @return int
     */
    protected static function get90Percentile($type)
    {
        static $percentile = [];
        if (!isset($percentile[$type])) {
            // Fake value that will cause the check to always evaluate to false.
            $percentile[$type] = 99999;

            $sql = "SELECT  COUNT(*) AS count
                    FROM    wcf1_blacklist_entry
                    WHERE   type = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$type]);
            $count = $statement->fetchSingleColumn();

            if ($count > 0) {
                $sql = "SELECT      occurrences
                        FROM        wcf1_blacklist_entry
                        WHERE       type = ?
                        ORDER BY    occurrences DESC";
                // Offset of the last row within the top 10%, rounded up.
                $statement = WCF::getDB()->prepare($sql, 1, \intdiv($count + 9, 10) - 1);
                $statement->execute([$type]);

                $percentile[$type] = $statement->fetchSingleColumn();
            }
        }

        return $percentile[$type];
    }
}
