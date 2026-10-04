<?php

namespace wcf\system\view\filter;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\user\option\UserOption;
use wcf\system\form\builder\field\AbstractFormField;

/**
 * Filter for the values of a user option, requires a list of users.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserOptionFilter extends AbstractFilter
{
    public function __construct(
        private readonly UserOption $option
    ) {
        parent::__construct(
            $option->getIdentifier(),
            'wcf.user.option.' . $option->optionName,
            'user_option_value.' . $option->getIdentifier()
        );
    }

    #[\Override]
    public function getFormField(): AbstractFormField
    {
        return $this->option->getFormOption()
            ->getFilterFormField($this->id, $this->option->getConfiguration())
            ->label($this->languageItem);
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $value): void
    {
        $this->joinUserOptionValues($list);

        $this->option->getFormOption()->applyFilter($list, $this->getDatabaseColumnName($list), $value);
    }

    #[\Override]
    public function renderValue(string $value): string
    {
        return $this->option->getFormOption()->renderFilterValue($value, $this->option->getConfiguration());
    }

    #[\Override]
    public function serializeValue(mixed $value): string
    {
        return $this->option->getFormOption()->serializeValue($value);
    }

    #[\Override]
    public function unserializeValue(string $value): mixed
    {
        return $this->option->getFormOption()->unserializeValue($value);
    }

    /**
     * Creates the filters for the given user options. The birthday is filtered
     * by the age, date options are skipped because they store `Y-m-d` instead
     * of the timestamps expected by the date range filter.
     *
     * @param UserOption[] $options
     * @param bool $respectBirthdayYearPrivacy skips users who hide the year of their birthday when filtering by age
     * @return list<UserOptionFilter|UserAgeFilter>
     */
    public static function createFilters(array $options, bool $respectBirthdayYearPrivacy = true): array
    {
        $filters = [];
        foreach ($options as $option) {
            $filter = match ($option->optionType) {
                'birthday' => new UserAgeFilter($option, $respectBirthdayYearPrivacy),
                'date' => null,
                default => new self($option),
            };

            if ($filter !== null) {
                $filters[] = $filter;
            }
        }

        return $filters;
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
