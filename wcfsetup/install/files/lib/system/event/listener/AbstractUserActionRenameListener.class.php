<?php

namespace wcf\system\event\listener;

use wcf\data\user\UserAction;
use wcf\system\WCF;

/**
 * Abstract implementation of an event listener updating database tables during
 * a user rename.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
abstract class AbstractUserActionRenameListener implements IParameterizedEventListener
{
    /**
     * data of the updated database tables
     * can either contain the database table as value if `userID` and `username`
     * are the names of the database columns or an array with values `name`
     * (database table name), `userID` and `username` (names of the database
     * table columns containing the id and name of the user)
     * `{WCF_N}` will be automatically replaced with the number of the WCF installation
     * (only with PHP 5.6 string concatenation is possible in property declarations)
     * @var array
     */
    protected $databaseTables = [];

    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        /** @var UserAction $eventObj */

        $userID = $eventObj->getObjects()[0]->userID;
        $username = $eventObj->getParameters()['data']['username'];

        WCF::getDB()->beginTransaction();

        foreach ($this->databaseTables as $databaseTable) {
            if (!\is_array($databaseTable)) {
                $databaseTable = ['name' => $databaseTable];
            }
            if (!isset($databaseTable['userID'])) {
                $databaseTable['userID'] = 'userID';
            }
            if (!isset($databaseTable['username'])) {
                $databaseTable['username'] = 'username';
            }

            $sql = "UPDATE  " . \str_replace('{WCF_N}', 1, $databaseTable['name']) . "
                    SET     " . $databaseTable['username'] . " = ?
                    WHERE   " . $databaseTable['userID'] . " = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$username, $userID]);
        }

        WCF::getDB()->commitTransaction();
    }
}
