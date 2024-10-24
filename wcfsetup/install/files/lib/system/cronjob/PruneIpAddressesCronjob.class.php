<?php

namespace wcf\system\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\system\WCF;

/**
 * Prunes old ip addresses.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 */
class PruneIpAddressesCronjob extends AbstractCronjob
{
    /**
     * list of columns
     * [
     *   <tableName> => [
     *      <ipAddressColumn> => <timestampColumn>
     *   ]
     * ]
     * @var string[][]
     */
    public $columns = [];

    /**
     * @inheritDoc
     */
    public function execute(Cronjob $cronjob)
    {
        if (!PRUNE_IP_ADDRESS) {
            return;
        }

        $this->columns['wcf1_user']['registrationIpAddress'] = 'registrationDate';

        parent::execute($cronjob);

        foreach ($this->columns as $tableName => $columnData) {
            foreach ($columnData as $ipAddressColumn => $timestampColumn) {
                $sql = "UPDATE  {$tableName}
                        SET     {$ipAddressColumn} = ?
                        WHERE   {$timestampColumn} <= ?
                            AND {$ipAddressColumn} <> ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([
                    '',
                    TIME_NOW - 86400 * PRUNE_IP_ADDRESS, // 86400 = 1 day
                    '',
                ]);
            }
        }
    }
}
