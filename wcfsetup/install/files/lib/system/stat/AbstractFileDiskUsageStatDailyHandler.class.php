<?php

namespace wcf\system\stat;

use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Abstract stat handler implementation for disk usage based on wcf1_file.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
abstract class AbstractFileDiskUsageStatDailyHandler implements IStatDailyHandler
{
    protected function getCounter(
        int $date,
        string $tableName,
        string $dateColumnName,
        string $fileIDColumnName = 'fileID'
    ): ?int {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add(
            \sprintf(
                'fileID IN (SELECT %s FROM %s WHERE %s BETWEEN ? AND ?)',
                $fileIDColumnName,
                $tableName,
                $dateColumnName,
            ),
            [
                $date,
                $date + 86399,
            ]
        );

        $sql = "SELECT  CEIL(SUM(fileSize) / 1000)
                FROM    wcf1_file
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        return $statement->fetchSingleColumn();
    }

    protected function getTotal(
        int $date,
        string $tableName,
        string $dateColumnName,
        string $fileIDColumnName = 'fileID'
    ): ?int {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add(
            \sprintf(
                'fileID IN (SELECT %s FROM %s WHERE %s < ?)',
                $fileIDColumnName,
                $tableName,
                $dateColumnName,
            ),
            [
                $date + 86399,
            ]
        );


        $sql = "SELECT  CEIL(SUM(fileSize) / 1000)
                FROM    wcf1_file
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
