<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\system\form\builder\field\NumericRangeFormField;
use wcf\system\object\filter\AbstractRangeObjectFilter;
use wcf\system\object\filter\IObjectListFilter;
use wcf\system\option\user\TJoinUserOptionValues;
use wcf\util\DateUtil;

/**
 * Filters users by their age, which is derived from a birthday user option.
 * The value consists of an optional minimum and an optional maximum age, both
 * inclusive. Guests and users without a birthday never match.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractRangeObjectFilter<User, int>
 * @implements IObjectListFilter<User, array{0: ?int, 1: ?int}>
 */
final class UserOptionAgeObjectFilter extends AbstractRangeObjectFilter implements IObjectListFilter
{
    use TJoinUserOptionValues;

    private const MAXIMUM_AGE = 120;

    public function __construct(
        private readonly UserOption $option,
    ) {}

    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userOption.' . $this->option->optionName;
    }

    #[\Override]
    protected function getLanguageItem(): string
    {
        return 'wcf.objectFilter.user.age';
    }

    #[\Override]
    public function isAvailable(): bool
    {
        return $this->option->isDisabled === 0
            && $this->option->validateOptions()
            && $this->option->validatePermissions();
    }

    #[\Override]
    protected function createFormField(): NumericRangeFormField
    {
        return NumericRangeFormField::create('userOption_' . \str_replace('.', '_', $this->option->optionName))
            ->label($this->getLanguageItem())
            ->integerValues()
            ->minimum(0)
            ->maximum(self::MAXIMUM_AGE);
    }

    #[\Override]
    protected function unserializeBound(string $bound): int
    {
        return (int)$bound;
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, mixed $value): void
    {
        [$from, $to] = $value;

        $this->joinUserOptionValues($list);

        // The birthday is stored as `Y-m-d`, which sorts chronologically. Users
        // without a birthday store either an empty string or `0000-00-00`.
        $columnName = 'user_option_value.' . $this->option->getIdentifier();
        $list->getConditionBuilder()->add("{$columnName} > ?", ['0000-00-00']);

        if ($from !== null) {
            $latestBirthday = DateUtil::getDateTimeByTimestamp(\TIME_NOW)
                ->sub(new \DateInterval("P{$from}Y"));

            $list->getConditionBuilder()->add("{$columnName} <= ?", [$latestBirthday->format('Y-m-d')]);
        }

        if ($to !== null) {
            $earliestBirthday = DateUtil::getDateTimeByTimestamp(\TIME_NOW)
                ->sub(new \DateInterval('P' . ($to + 1) . 'Y'))
                ->add(new \DateInterval('P1D'));

            $list->getConditionBuilder()->add("{$columnName} >= ?", [$earliestBirthday->format('Y-m-d')]);
        }
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        if ($object->isGuest()) {
            return false;
        }

        $birthday = (string)($object->getUserOption($this->option->optionName) ?? '');
        if ($birthday === '' || $birthday === '0000-00-00') {
            return false;
        }

        [$from, $to] = $configuredValue;
        $age = DateUtil::getAge($birthday);

        if ($from !== null && $age < $from) {
            return false;
        }
        if ($to !== null && $age > $to) {
            return false;
        }

        return true;
    }
}
