<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\object\filter\IObjectFilter;
use wcf\system\user\UserBirthdayCache;
use wcf\system\WCF;

/**
 * Filters users by whether it is their birthday today, based on the time zone
 * of the user. Guests never match.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @implements IObjectFilter<User, bool>
 */
final class UserBirthdayObjectFilter implements IObjectFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userBirthday';
    }

    #[\Override]
    public function getTitle(): string
    {
        return WCF::getLanguage()->get('wcf.objectFilter.user.birthday');
    }

    #[\Override]
    public function getFormField(): BooleanFormField
    {
        return BooleanFormField::create('userBirthday')
            ->label('wcf.objectFilter.user.birthday');
    }

    #[\Override]
    public function serializeValue(mixed $value): string
    {
        return $value ? '1' : '0';
    }

    #[\Override]
    public function unserializeValue(string $serializedValue): bool
    {
        return (bool)$serializedValue;
    }

    #[\Override]
    public function toFormFieldValue(mixed $value): bool
    {
        return $value;
    }

    #[\Override]
    public function summarizeValue(mixed $value): string
    {
        if ($value) {
            return WCF::getLanguage()->get('wcf.objectFilter.user.birthday.summary.yes');
        }

        return WCF::getLanguage()->get('wcf.objectFilter.user.birthday.summary.no');
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
