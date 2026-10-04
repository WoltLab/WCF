<?php

namespace wcf\system\view\filter;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\system\view\filter\exception\InvalidFilterValue;
use wcf\util\DateUtil;

/**
 * Filters users by their age that is derived from the birthday user option,
 * requires a list of users. Users who hide the year of their birthday are
 * never matched unless `$respectShowYear` is disabled, which is reserved for
 * administrative views.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserAgeFilter extends IntegerFilter
{
    private const MAXIMUM_AGE = 120;

    public function __construct(
        UserOption $birthdayOption,
        private readonly bool $respectShowYear = true,
    ) {
        parent::__construct(
            'age',
            'wcf.user.birthday.age',
            'user_option_value.' . $birthdayOption->getIdentifier(),
            minimum: 0,
            maximum: self::MAXIMUM_AGE
        );
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $value): void
    {
        $values = \explode(';', $value);
        $ageFrom = \count($values) === 2 ? (int)$values[0] : 0;
        $ageTo = \count($values) === 2 ? (int)$values[1] : 0;

        if (
            ($ageFrom === 0 && $ageTo === 0)
            || $ageFrom < 0
            || $ageTo < 0
            || $ageFrom > self::MAXIMUM_AGE
            || $ageTo > self::MAXIMUM_AGE
        ) {
            throw new InvalidFilterValue("Invalid value '{$value}' for filter '{$this->id}' given.");
        }

        $this->joinUserOptionValues($list);

        $showYearOptionID = User::getUserOptionID('birthdayShowYear');
        if ($this->respectShowYear && $showYearOptionID !== null) {
            $list->getConditionBuilder()->add("user_option_value.userOption{$showYearOptionID} = ?", [1]);
        }

        // The birthday is stored as `Y-m-d`, which sorts chronologically. Users
        // without a birthday store either an empty string or `0000-00-00`.
        $columnName = $this->getDatabaseColumnName($list);
        $list->getConditionBuilder()->add("{$columnName} > ?", ['0000-00-00']);

        if ($ageFrom !== 0) {
            $latestBirthday = DateUtil::getDateTimeByTimestamp(\TIME_NOW)
                ->sub(new \DateInterval("P{$ageFrom}Y"));

            $list->getConditionBuilder()->add("{$columnName} <= ?", [$latestBirthday->format('Y-m-d')]);
        }

        if ($ageTo !== 0) {
            $earliestBirthday = DateUtil::getDateTimeByTimestamp(\TIME_NOW)
                ->sub(new \DateInterval('P' . ($ageTo + 1) . 'Y'))
                ->add(new \DateInterval('P1D'));

            $list->getConditionBuilder()->add("{$columnName} >= ?", [$earliestBirthday->format('Y-m-d')]);
        }
    }

    /**
     * Joins the user option values when counting the objects or reading their
     * ids, `UserList` only joins them when reading the objects.
     *
     * @param DatabaseObjectList<DatabaseObject> $list
     */
    private function joinUserOptionValues(DatabaseObjectList $list): void
    {
        if (\str_contains($list->sqlConditionJoins, 'wcf1_user_option_value user_option_value')) {
            return;
        }

        $list->sqlConditionJoins .= "
            LEFT JOIN   wcf1_user_option_value user_option_value
            ON          user_option_value.userID = user_table.userID";
    }
}
