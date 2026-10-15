<?php

/**
 * Migrates the legacy conditions of notices, ads, user group assignments and
 * trophies into object filters. Conditions that cannot be converted, e.g. those
 * of apps, are kept and the affected notices, ads, assignments and trophies are
 * disabled.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\condition\DaysOfWeekCondition;
use wcf\system\condition\page\MultiPageCondition;
use wcf\system\condition\TimeCondition;
use wcf\system\condition\UserAvatarCondition;
use wcf\system\condition\UserBirthdayCondition;
use wcf\system\condition\UserCoverPhotoCondition;
use wcf\system\condition\UserEmailCondition;
use wcf\system\condition\UserGroupCondition;
use wcf\system\condition\UserIntegerPropertyCondition;
use wcf\system\condition\UserLanguageCondition;
use wcf\system\condition\UserMobileBrowserCondition;
use wcf\system\condition\UserMultifactorCondition;
use wcf\system\condition\UserRegistrationDateCondition;
use wcf\system\condition\UserRegistrationDateIntervalCondition;
use wcf\system\condition\UserSignatureCondition;
use wcf\system\condition\UserStateCondition;
use wcf\system\condition\UserTrophyCondition;
use wcf\system\condition\UserUsernameCondition;
use wcf\system\object\filter\migration\LegacyConditionMigration;
use wcf\system\WCF;

/**
 * Returns the given ids as a comma-separated list, or `null` if there are none.
 */
$idList = static function (mixed $ids): ?string {
    if (!\is_array($ids) || $ids === []) {
        return null;
    }

    return \implode(',', \array_map(static fn($id) => (int)$id, $ids));
};

/**
 * Returns one filter per id, the ids are optional.
 *
 * @return list<array{0: string, 1: string}>
 */
$filterPerID = static function (string $identifier, mixed $ids): array {
    if (!\is_array($ids)) {
        return [];
    }

    return \array_values(\array_map(
        static fn($id) => [$identifier, (string)(int)$id],
        $ids,
    ));
};

/**
 * Converts a legacy yes/no value (`0` or `1`) into a boolean filter.
 *
 * @return ?list<array{0: string, 1: string}>
 */
$boolean = static function (string $identifier, mixed $value): ?array {
    return match ((int)$value) {
        0 => [[$identifier, '0']],
        1 => [[$identifier, '1']],
        default => null,
    };
};

/**
 * Returns the legacy types of the user options by their name, the types are
 * converted after the conditions have been migrated.
 *
 * @return array<string, string>
 */
$getUserOptionTypes = static function (): array {
    static $optionTypes = null;

    if ($optionTypes === null) {
        $sql = "SELECT  optionName, optionType
                FROM    wcf1_user_option";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        $optionTypes = $statement->fetchMap('optionName', 'optionType');
    }

    return $optionTypes;
};

/**
 * Returns a converter for the user state, the banned state is dropped if there
 * is no matching filter.
 *
 * @return \Closure(array<string, mixed>): ?list<array{0: string, 1: string}>
 */
$userState = static function (bool $includeBanned) use ($boolean): \Closure {
    $identifiers = [
        'userIsEnabled' => 'com.woltlab.wcf.userActivated',
        'userIsEmailConfirmed' => 'com.woltlab.wcf.userEmailConfirmed',
    ];
    if ($includeBanned) {
        $identifiers['userIsBanned'] = 'com.woltlab.wcf.userBanned';
    }

    return static function (array $data) use ($boolean, $identifiers): ?array {
        $filters = [];
        foreach ($identifiers as $key => $identifier) {
            if (!isset($data[$key])) {
                continue;
            }

            $filter = $boolean($identifier, $data[$key]);
            if ($filter === null) {
                return null;
            }

            \array_push($filters, ...$filter);
        }

        return $filters;
    };
};

$converters = [
    MultiPageCondition::class => static function (array $data) use ($idList): ?array {
        $pageIDs = $idList($data['pageIDs'] ?? null);
        if ($pageIDs === null) {
            return null;
        }

        // Older conditions do not store the reverse logic flag.
        $reverseLogic = (bool)($data['pageIDs_reverseLogic'] ?? false);
        $identifier = $reverseLogic ? 'com.woltlab.wcf.notRequestedPage' : 'com.woltlab.wcf.requestedPage';

        return [[$identifier, $pageIDs]];
    },
    DaysOfWeekCondition::class => static function (array $data) use ($idList): ?array {
        $days = $idList($data['daysOfWeek'] ?? null);
        if ($days === null) {
            return null;
        }

        return [['com.woltlab.wcf.daysOfWeek', $days]];
    },
    TimeCondition::class => static function (array $data): ?array {
        // The filter always uses the time zone of the user.
        if ((string)($data['timezone'] ?? '') !== '') {
            return null;
        }

        $times = [];
        foreach (['startTime', 'endTime'] as $key) {
            $time = (string)($data[$key] ?? '');
            if ($time === '') {
                $times[] = '';

                continue;
            }

            // Out of range values like `25:00` overflow into the next day.
            $dateTime = \DateTimeImmutable::createFromFormat('!H:i', $time, new \DateTimeZone('UTC'));
            if ($dateTime === false || $dateTime->format('Y-m-d') !== '1970-01-01') {
                return null;
            }

            $times[] = $dateTime->format('H:i');
        }

        [$startTime, $endTime] = $times;
        if ($startTime === $endTime) {
            return null;
        }

        return [['com.woltlab.wcf.timeRange', $startTime . ';' . $endTime]];
    },
    UserUsernameCondition::class => static function (array $data): ?array {
        $username = (string)($data['username'] ?? '');
        if ($username === '') {
            return null;
        }

        return [['com.woltlab.wcf.userUsername', $username]];
    },
    UserEmailCondition::class => static function (array $data): ?array {
        $email = (string)($data['email'] ?? '');
        if ($email === '') {
            return null;
        }

        return [['com.woltlab.wcf.userEmail', $email]];
    },
    UserGroupCondition::class => static function (array $data) use ($filterPerID): array {
        return [
            ...$filterPerID('com.woltlab.wcf.userGroup', $data['groupIDs'] ?? null),
            ...$filterPerID('com.woltlab.wcf.userNotInGroup', $data['notGroupIDs'] ?? null),
        ];
    },
    UserLanguageCondition::class => static function (array $data) use ($idList): ?array {
        $languageIDs = $idList($data['languageIDs'] ?? null);
        if ($languageIDs === null) {
            return null;
        }

        return [['com.woltlab.wcf.userLanguage', $languageIDs]];
    },
    UserRegistrationDateCondition::class => static function (array $data): ?array {
        $start = (string)($data['registrationDateStart'] ?? '');
        $end = (string)($data['registrationDateEnd'] ?? '');
        if ($start === '' && $end === '') {
            return null;
        }

        return [['com.woltlab.wcf.userRegistrationDate', $start . ';' . $end]];
    },
    UserRegistrationDateIntervalCondition::class => static function (array $data): ?array {
        $greaterThan = isset($data['greaterThan']) ? (string)(int)$data['greaterThan'] : '';
        $lessThan = isset($data['lessThan']) ? (string)(int)$data['lessThan'] : '';
        if ($greaterThan === '' && $lessThan === '') {
            return null;
        }

        return [['com.woltlab.wcf.userRegistrationDays', $greaterThan . ';' . $lessThan]];
    },
    UserAvatarCondition::class => static function (array $data) use ($boolean): ?array {
        // The value `2` (Gravatar) is no longer supported and never matched.
        return $boolean('com.woltlab.wcf.userAvatar', $data['userAvatar'] ?? null);
    },
    UserSignatureCondition::class => static function (array $data) use ($boolean): ?array {
        return $boolean('com.woltlab.wcf.userSignature', $data['userSignature'] ?? null);
    },
    UserCoverPhotoCondition::class => static function (array $data) use ($boolean): ?array {
        return $boolean('com.woltlab.wcf.userCoverPhoto', $data['userCoverPhoto'] ?? null);
    },
    UserStateCondition::class => $userState(includeBanned: true),
    UserMobileBrowserCondition::class => static function (array $data) use ($boolean): ?array {
        return $boolean('com.woltlab.wcf.userMobileBrowser', $data['usesMobileBrowser'] ?? null);
    },
    UserBirthdayCondition::class => static function (array $data) use ($boolean): ?array {
        return $boolean('com.woltlab.wcf.userBirthday', $data['birthdayToday'] ?? null);
    },
    UserMultifactorCondition::class => static function (array $data) use ($boolean): ?array {
        return $boolean('com.woltlab.wcf.userMultifactor', $data['multifactorActive'] ?? null);
    },
    // Properties of apps are migrated by the apps, which provide the matching filters.
    UserIntegerPropertyCondition::class => LegacyConditionMigration::getUserIntegerPropertyConverter([
        'activityPoints',
        'likesReceived',
        'trophyPoints',
    ]),
    UserTrophyCondition::class => static function (array $data) use ($filterPerID): array {
        return [
            ...$filterPerID('com.woltlab.wcf.userTrophy', $data['userTrophyIDs'] ?? null),
            ...$filterPerID('com.woltlab.wcf.userNoTrophy', $data['notUserTrophyIDs'] ?? null),
        ];
    },
    // Options that no longer exist or whose type is not supported keep the object disabled.
    // The class `UserOptionsCondition` has been removed, its name is used as the key.
    'wcf\\system\\condition\\UserOptionsCondition' => static function (array $data) use ($getUserOptionTypes): ?array {
        $optionTypes = $getUserOptionTypes();

        $filters = [];
        foreach ($data['optionValues'] ?? [] as $optionName => $value) {
            if (!isset($optionTypes[$optionName])) {
                return null;
            }

            $identifier = 'com.woltlab.wcf.userOption.' . $optionName;
            switch ($optionTypes[$optionName]) {
                case 'birthday':
                    $ageFrom = (int)($value['ageFrom'] ?? 0);
                    $ageTo = (int)($value['ageTo'] ?? 0);
                    if ($ageFrom !== 0 || $ageTo !== 0) {
                        $filters[] = [$identifier, ($ageFrom ?: '') . ';' . ($ageTo ?: '')];
                    }
                    break;

                case 'boolean':
                    // The legacy condition only matched users who enabled the option.
                    if ((int)$value === 1) {
                        $filters[] = [$identifier, '1'];
                    }
                    break;

                case 'checkboxes':
                case 'multiSelect':
                    $selectedValues = \is_array($value) ? $value : \explode("\n", (string)$value);
                    $selectedValues = \array_filter(
                        \array_map(static fn($selectedValue) => \trim((string)$selectedValue), $selectedValues),
                        static fn(string $selectedValue) => $selectedValue !== ''
                    );

                    // The filter is not repeatable, the selected values are
                    // stored comma-separated like the values of the option.
                    if ($selectedValues !== []) {
                        $filters[] = [$identifier, \implode(',', $selectedValues)];
                    }
                    break;

                case 'integer':
                case 'float':
                    // The legacy condition matched the value as text, which is
                    // converted into the exact value.
                    $number = (int)$value;
                    if ($number === 0) {
                        return null;
                    }

                    $filters[] = [$identifier, $number . ';' . $number];
                    break;

                case 'aboutMe':
                case 'date':
                case 'message':
                case 'password':
                case 'wysiwyg':
                    return null;

                default:
                    $value = \trim((string)$value);
                    if ($value !== '') {
                        $filters[] = [$identifier, $value];
                    }
                    break;
            }
        }

        return $filters;
    },
];

// Notices do not provide a filter for banned users, who cannot access the site anyway.
(new LegacyConditionMigration('com.woltlab.wcf.condition.notice', 'wcf1_notice', 'noticeID'))
    ->migrate([
        ...$converters,
        UserStateCondition::class => $userState(includeBanned: false),
    ]);
(new LegacyConditionMigration('com.woltlab.wcf.condition.ad', 'wcf1_ad', 'adID'))
    ->migrate($converters);
(new LegacyConditionMigration('com.woltlab.wcf.condition.userGroupAssignment', 'wcf1_user_group_assignment', 'assignmentID'))
    ->migrate($converters);

// The legacy form stored the conditions of trophies regardless of whether they
// are awarded automatically. Those conditions were never evaluated and must not
// cause the migration to disable the trophies.
$sql = "DELETE FROM wcf1_condition
        WHERE       objectID IN (
                        SELECT  trophyID
                        FROM    wcf1_trophy
                        WHERE   awardAutomatically = ?
                    )
                AND objectTypeID IN (
                        SELECT      object_type.objectTypeID
                        FROM        wcf1_object_type object_type
                        INNER JOIN  wcf1_object_type_definition definition
                        ON          definition.definitionID = object_type.definitionID
                        WHERE       definition.definitionName = ?
                    )";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([
    0,
    'com.woltlab.wcf.condition.trophy',
]);

(new LegacyConditionMigration('com.woltlab.wcf.condition.trophy', 'wcf1_trophy', 'trophyID'))
    ->migrate($converters);
