<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\object\filter\AbstractBooleanObjectFilter;
use wcf\system\user\UserBirthdayCache;

/**
 * Filters users by whether it is their birthday today, based on the time zone
 * of the user. Guests never match.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractBooleanObjectFilter<User>
 */
final class UserBirthdayObjectFilter extends AbstractBooleanObjectFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userBirthday';
    }

    #[\Override]
    protected function getLanguageItem(): string
    {
        return 'wcf.objectFilter.user.birthday';
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        if ($object->isGuest()) {
            return false;
        }

        $today = new \DateTimeImmutable('now', $object->getTimeZone());
        $userIDs = UserBirthdayCache::getInstance()->getBirthdays(
            (int)$today->format('n'),
            (int)$today->format('j'),
        );

        return $configuredValue === \in_array($object->userID, $userIDs, true);
    }
}
