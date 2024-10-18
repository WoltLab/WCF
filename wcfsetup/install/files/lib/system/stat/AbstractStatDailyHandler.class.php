<?php

namespace wcf\system\stat;

use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Abstract implementation of a stat handler.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractStatDailyHandler implements IStatDailyHandler
{
    /**
     * Counts the number of rows for a single day.
     *
     * @param int $date
     * @param string $tableName
     * @param string $dateColumnName
     * @return  int
     */
    protected function getCounter($date, $tableName, $dateColumnName)
    {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add($dateColumnName . ' BETWEEN ? AND ?', [$date, $date + 86399]);

        $this->addConditions($conditionBuilder);

        $sql = "SELECT  COUNT(*)
                FROM    " . $tableName . "
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        return $statement->fetchSingleColumn();
    }

    /**
     * Counts the total number of rows.
     *
     * @param int $date
     * @param string $tableName
     * @param string $dateColumnName
     * @return  int
     */
    protected function getTotal($date, $tableName, $dateColumnName)
    {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add($dateColumnName . ' < ?', [$date + 86399]);

        $this->addConditions($conditionBuilder);

        $sql = "SELECT  COUNT(*)
                FROM    " . $tableName . "
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        return $statement->fetchSingleColumn();
    }

    /**
     * @inheritDoc
     */
    public function getFormattedCounter($counter)
    {
        return $counter;
    }

    /**
     * Adds additional conditions to the given condition builder.
     *
     * @param PreparedStatementConditionBuilder $conditionBuilder
     * @since   3.1
     */
    protected function addConditions(PreparedStatementConditionBuilder $conditionBuilder)
    {
        // does nothing
    }
}
