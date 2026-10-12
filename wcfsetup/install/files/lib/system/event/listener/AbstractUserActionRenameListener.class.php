<?php

namespace wcf\system\event\listener;

use wcf\command\user\UpdateUsernameColumn;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\user\rename\UsernameColumn;
use wcf\system\WCF;

/**
 * Abstract implementation of an event listener updating database tables during
 * a user rename.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Listen for the `UserRenamed` event and use `UserRenamed::updateUsernameColumn()` instead.
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
     * @var array<string|array{name: string, userID?: string, username?: string}>
     */
    protected $databaseTables = [];

    #[\Override]
    public function execute(mixed $eventObj, string $className, string $eventName, array &$parameters)
    {
        \assert($eventObj instanceof UserAction);

        // The objects of the action can still contain the old username.
        $user = new User($eventObj->getObjects()[0]->userID);

        WCF::getDB()->beginTransaction();

        foreach ($this->databaseTables as $databaseTable) {
            if (!\is_array($databaseTable)) {
                $databaseTable = ['name' => $databaseTable];
            }

            $column = new UsernameColumn(
                \str_replace('{WCF_N}', '1', $databaseTable['name']),
                $databaseTable['userID'] ?? 'userID',
                $databaseTable['username'] ?? 'username',
            );

            (new UpdateUsernameColumn($column, $user))();
        }

        WCF::getDB()->commitTransaction();
    }
}
