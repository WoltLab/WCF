<?php

namespace wcf\system\view\filter;

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
    private const JOIN_CONDITION = "
        LEFT JOIN   wcf1_user_option_value user_option_value
        ON          user_option_value.userID = user_table.userID";

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
        // `UserList` only joins the user option values for reading the objects,
        // but not for counting them or reading their ids.
        if (!\str_contains($list->sqlConditionJoins, 'wcf1_user_option_value user_option_value')) {
            $list->sqlConditionJoins .= self::JOIN_CONDITION;
        }

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
}
