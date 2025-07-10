<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;
use wcf\system\form\builder\container\PrefixConditionFormFieldContainer;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\util\DateUtil;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class RegistrationDaysUserConditionType extends IntegerUserConditionType
{
    public function __construct()
    {
        parent::__construct('registrationDays', 'registrationDate', 'com.woltlab.wcf.user.registrationDateInterval');
    }

    #[\Override]
    public function getFormField(string $id): PrefixConditionFormFieldContainer
    {
        $container = parent::getFormField($id);
        $field = $container->getField();
        \assert($field instanceof IntegerFormField);
        $field->suffix("wcf.acp.option.suffix.days");

        return $container;
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $objectList): void
    {
        ["condition" => $condition, "timestamp" => $timestamp] = $this->getParsedFilter();

        $objectList->getConditionBuilder()->add(
            "? {$condition} {$objectList->getDatabaseTableAlias()}.registrationDate",
            [$timestamp]
        );
    }

    #[\Override]
    public function matches(object $object): bool
    {
        ["condition" => $condition, "timestamp" => $timestamp] = $this->getParsedFilter();

        return match ($condition) {
            '>' => $timestamp > $object->registrationDate,
            '<' => $timestamp < $object->registrationDate,
            '>=' => $timestamp >= $object->registrationDate,
            '<=' => $timestamp <= $object->registrationDate,
            default => throw new \InvalidArgumentException("Unknown condition: {$condition}"),
        };
    }

    /**
     * @return array{condition: string, timestamp: int}
     */
    private function getParsedFilter(): array
    {
        if (!isset($this->filter['condition'], $this->filter['value'])) {
            throw new \InvalidArgumentException("Invalid filter format");
        }

        $date = DateUtil::getDateTimeByTimestamp(TIME_NOW);
        $date->setTimezone(new \DateTimeZone(TIMEZONE));
        $date->sub(new \DateInterval("P{$this->filter['value']}D"));

        return [
            'condition' => $this->filter['condition'],
            'timestamp' => $date->getTimestamp(),
        ];
    }

    #[\Override]
    protected function getConditions(): array
    {
        return [">", "<", ">=", "<="];
    }
}
