<?php

namespace wcf\system\stat;

use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Abstract stat handler implementation for disk usage.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractDiskUsageStatDailyHandler extends AbstractStatDailyHandler
{
    /**
     * name of the filesize database table column
     * @var string
     */
    protected $columnName = 'filesize';

    #[\Override]
    protected function getCounter($date, $tableName, $dateColumnName)
    {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add($dateColumnName . ' BETWEEN ? AND ?', [$date, $date + 86399]);

        $this->addConditions($conditionBuilder);

        $sql = "SELECT  CEIL(SUM(" . $this->columnName . ") / 1000)
                FROM    " . $tableName . "
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        return $statement->fetchSingleColumn();
    }

    #[\Override]
    protected function getTotal($date, $tableName, $dateColumnName)
    {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add($dateColumnName . ' < ?', [$date + 86399]);

        $this->addConditions($conditionBuilder);

        $sql = "SELECT  CEIL(SUM(" . $this->columnName . ") / 1000)
                FROM    " . $tableName . "
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        return $statement->fetchSingleColumn();
    }

    #[\Override]
    public function getFormattedCounter(int $counter)
    {
        return \round($counter / 1000, 2); // return mb
    }
}
