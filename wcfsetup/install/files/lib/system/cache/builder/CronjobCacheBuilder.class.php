<?php

namespace wcf\system\cache\builder;

use wcf\system\WCF;

/**
 * Caches cronjob information.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class CronjobCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    public function rebuild(array $parameters)
    {
        $sql = "SELECT  MIN(nextExec) AS nextExec,
                        MIN(afterNextExec) AS afterNextExec
                FROM    wcf1_cronjob
                WHERE   isDisabled = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([0]);
        $row = $statement->fetchArray();

        return [
            'afterNextExec' => $row['afterNextExec'],
            'nextExec' => $row['nextExec'],
        ];
    }
}
