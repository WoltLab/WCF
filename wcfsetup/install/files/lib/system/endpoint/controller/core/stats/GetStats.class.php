<?php

namespace wcf\system\endpoint\controller\core\stats;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\http\Helper;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\stat\IStatDailyHandler;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Returns the daily statistics of the requested types, grouped by the requested time unit.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[GetRequest('/core/stats')]
final class GetStats implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        if (!WCF::getSession()->hasPermission('admin.management.canViewLog')) {
            throw new PermissionDeniedException();
        }

        $parameters = Helper::mapApiParameters($request, GetStatsParameters::class);

        if (!$this->isValidDate($parameters->startDate)) {
            throw new UserInputException('startDate');
        }
        if (!$this->isValidDate($parameters->endDate)) {
            throw new UserInputException('endDate');
        }

        $startDate = $parameters->startDate;
        $endDate = $parameters->endDate;
        if ($endDate < $startDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $availableObjectTypes = [];
        foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.statDailyHandler') as $objectType) {
            $availableObjectTypes[$objectType->objectTypeID] = $objectType;
        }

        $objectTypes = [];
        foreach ($parameters->objectTypeIDs as $objectTypeID) {
            if (!isset($availableObjectTypes[$objectTypeID])) {
                throw new UserInputException('objectTypeIDs');
            }

            $objectTypes[$objectTypeID] = $availableObjectTypes[$objectTypeID];
        }

        return new JsonResponse(
            $this->getSeries($objectTypes, $startDate, $endDate, $parameters->dateGrouping, $parameters->value)
        );
    }

    private function isValidDate(string $date): bool
    {
        $dateTime = \DateTimeImmutable::createFromFormat('!Y-m-d', $date, new \DateTimeZone('UTC'));

        return $dateTime !== false && $dateTime->format('Y-m-d') === $date;
    }

    /**
     * @param array<int, ObjectType> $objectTypes
     * @param 'daily'|'weekly'|'monthly'|'yearly' $dateGrouping
     * @param 'counter'|'total' $value
     * @return list<array{objectTypeID: int, label: string, data: list<array{date: string, value: mixed}>}>
     */
    private function getSeries(
        array $objectTypes,
        string $startDate,
        string $endDate,
        string $dateGrouping,
        string $value,
    ): array {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('objectTypeID IN (?)', [\array_keys($objectTypes)]);
        $conditionBuilder->add('date BETWEEN ? AND ?', [$startDate, $endDate]);

        $firstDayOfTheWeek = DateUtil::getFirstDayOfTheWeek();
        // Mode 3 starts the week on Monday, mode 0 on Sunday.
        $weekMode = $firstDayOfTheWeek === 1 ? 3 : 0;

        $sql = match ($dateGrouping) {
            'daily' => "SELECT      date, counter, total, objectTypeID
                        FROM        wcf1_stat_daily
                        " . $conditionBuilder . "
                        ORDER BY    date",
            'weekly' => "SELECT     MIN(date) AS date, SUM(counter) AS counter, MAX(total) AS total, objectTypeID
                         FROM       wcf1_stat_daily
                         " . $conditionBuilder . "
                         GROUP BY   YEARWEEK(date, " . $weekMode . "), objectTypeID
                         ORDER BY   date",
            'monthly' => "SELECT    MIN(date) AS date, SUM(counter) AS counter, MAX(total) AS total, objectTypeID
                          FROM      wcf1_stat_daily
                          " . $conditionBuilder . "
                          GROUP BY  EXTRACT(YEAR_MONTH FROM date), objectTypeID
                          ORDER BY  date",
            'yearly' => "SELECT     MIN(date) AS date, SUM(counter) AS counter, MAX(total) AS total, objectTypeID
                         FROM       wcf1_stat_daily
                         " . $conditionBuilder . "
                         GROUP BY   EXTRACT(YEAR FROM date), objectTypeID
                         ORDER BY   date",
        };

        // The limit caps the points per series, but applies to the rows of all series combined.
        $limit = match ($dateGrouping) {
            'daily' => 365,
            'weekly' => 260,
            'monthly', 'yearly' => 0,
        } * \count($objectTypes);

        $statement = WCF::getDB()->prepare($sql, $limit);
        $statement->execute($conditionBuilder->getParameters());

        $series = [];
        while ($row = $statement->fetchArray()) {
            $objectType = $objectTypes[$row['objectTypeID']];
            if (!isset($series[$row['objectTypeID']])) {
                $series[$row['objectTypeID']] = [
                    'objectTypeID' => $row['objectTypeID'],
                    'label' => WCF::getLanguage()->get('wcf.acp.stat.' . $objectType->objectType),
                    'data' => [],
                ];
            }

            $processor = $objectType->getProcessor();
            \assert($processor instanceof IStatDailyHandler);

            // `SUM()` yields a `DECIMAL`, which is returned as a string.
            $counter = (int)($value === 'total' ? $row['total'] : $row['counter']);

            $series[$row['objectTypeID']]['data'][] = [
                'date' => $this->getPeriodStart($row['date'], $dateGrouping, $firstDayOfTheWeek),
                'value' => $processor->getFormattedCounter($counter),
            ];
        }

        return \array_values($series);
    }

    /**
     * Returns the first day of the period containing the given date, which places
     * the points of all series at the same positions.
     *
     * @param 'daily'|'weekly'|'monthly'|'yearly' $dateGrouping
     */
    private function getPeriodStart(string $date, string $dateGrouping, int $firstDayOfTheWeek): string
    {
        return match ($dateGrouping) {
            'daily' => $date,
            'weekly' => $this->getWeekStart($date, $firstDayOfTheWeek),
            'monthly' => \substr($date, 0, 8) . '01',
            'yearly' => \substr($date, 0, 5) . '01-01',
        };
    }

    private function getWeekStart(string $date, int $firstDayOfTheWeek): string
    {
        $day = new \DateTimeImmutable($date, new \DateTimeZone('UTC'));
        $offset = ((int)$day->format('w') - $firstDayOfTheWeek + 7) % 7;

        return $day->sub(new \DateInterval("P{$offset}D"))->format('Y-m-d');
    }
}

/** @internal */
final class GetStatsParameters
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $startDate,
        /** @var non-empty-string */
        public readonly string $endDate,
        /** @var non-empty-list<positive-int> */
        public readonly array $objectTypeIDs,
        /** @var 'daily'|'weekly'|'monthly'|'yearly' */
        public readonly string $dateGrouping,
        /** @var 'counter'|'total' */
        public readonly string $value = 'counter',
    ) {}
}
