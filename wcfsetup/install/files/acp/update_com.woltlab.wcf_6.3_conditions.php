<?php

/**
 * Migrates the legacy conditions of notices, ads and user group assignments
 * into object filters. Conditions that cannot be converted, e.g. those of apps,
 * are kept and the affected notices, ads and assignments are disabled.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\condition\DaysOfWeekCondition;
use wcf\system\condition\page\MultiPageCondition;
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
    UserStateCondition::class => static function (array $data) use ($boolean): ?array {
        $filters = [];
        foreach (
            [
                'userIsBanned' => 'com.woltlab.wcf.userBanned',
                'userIsEnabled' => 'com.woltlab.wcf.userActivated',
                'userIsEmailConfirmed' => 'com.woltlab.wcf.userEmailConfirmed',
            ] as $key => $identifier
        ) {
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
    },
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
];

(new LegacyConditionMigration('com.woltlab.wcf.condition.notice', 'wcf1_notice', 'noticeID'))
    ->migrate($converters);
(new LegacyConditionMigration('com.woltlab.wcf.condition.ad', 'wcf1_ad', 'adID'))
    ->migrate($converters);
(new LegacyConditionMigration('com.woltlab.wcf.condition.userGroupAssignment', 'wcf1_user_group_assignment', 'assignmentID'))
    ->migrate($converters);
