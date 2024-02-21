<?php

namespace wcf\data\blacklist\entry;

use wcf\data\DatabaseObject;
use wcf\system\database\util\PreparedStatementConditionBuilder;
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
                        ['ipv6', [$ipv6TwoParts, $ipv6ThreeParts, $ipv6FourParts]]
                    );
                }
            }
        }

        $sql = "SELECT  type, occurrences
                FROM    wcf" . WCF_N . "_blacklist_entry
                " . $conditions;
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($conditions->getParameters());
        $matches = [];
        while ($row = $statement->fetchArray()) {
            if (self::isMatch($row['type'], $row['occurrences'])) {
                $matches[] = ($row['type'] === 'ipv4' || $row['type'] === 'ipv6') ? 'ip' : $row['type'];
            }
        }

        return $matches;
    }

    protected static function getHash($string)
    {
        return \hash('sha256', $string, true);
    }

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

    protected static function get90Percentile($type)
    {
        static $percentile = [];
        if (!isset($percentile[$type])) {
            // Fake value that will cause the check to always evaluate to false.
            $percentile[$type] = 99999;

            $sql = "SELECT  COUNT(*) AS count
                    FROM    wcf" . WCF_N . "_blacklist_entry
                    WHERE   type = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$type]);
            $count = $statement->fetchSingleColumn();

            if ($count > 0) {
                $sql = "SELECT      occurrences
                        FROM        wcf" . WCF_N . "_blacklist_entry
                        WHERE       type = ?
                        ORDER BY    occurrences DESC";
                $statement = WCF::getDB()->prepareStatement($sql, 1, \round($count * 0.9));
                $statement->execute([$type]);

                $percentile[$type] = $statement->fetchSingleColumn();
            }
        }

        return $percentile[$type];
    }
}
