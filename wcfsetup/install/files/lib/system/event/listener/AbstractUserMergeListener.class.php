<?php

namespace wcf\system\event\listener;

use wcf\acp\form\UserMergeForm;
use wcf\command\user\ReassignUserColumn;
use wcf\system\user\merge\UserColumn;

/**
 * Abstract implementation of an event listener updating database tables during
 * a user merge.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Listen for the `UsersMerging` event and use `UsersMerging::reassignUserColumn()` instead.
 */
abstract class AbstractUserMergeListener implements IParameterizedEventListener
{
    /**
     * data of the updated database tables
     * can either contain the database table as value if `userID` is the name
     * of the database column and neither username update nor ignore is needed
     * or an array with values `name` (database table name), `userID` (name of
     * the database table column containing the id of the user), `username`
     * (optional; database column name for the username if it also needs to be
     * updated, default value: username) and `ignore` (optional if an UPDATE
     * IGNORE query should be used)
     * `{WCF_N}` will be automatically replaced with the number of the WCF installation
     * @var array<string|array{name: string, userID?: string, username?: ?string, ignore?: bool}>
     */
    protected $databaseTables = [];

    #[\Override]
    public function execute(mixed $eventObj, string $className, string $eventName, array &$parameters)
    {
        \assert($eventObj instanceof UserMergeForm);

        $destinationUser = $eventObj->users[$eventObj->destinationUserID];

        foreach ($this->databaseTables as $databaseTable) {
            if (!\is_array($databaseTable)) {
                $databaseTable = ['name' => $databaseTable];
            }

            $usernameColumn = \array_key_exists('username', $databaseTable) ? $databaseTable['username'] : 'username';

            $column = new UserColumn(
                \str_replace('{WCF_N}', '1', $databaseTable['name']),
                $databaseTable['userID'] ?? 'userID',
                $usernameColumn !== '' ? $usernameColumn : null,
                $databaseTable['ignore'] ?? false,
            );

            (new ReassignUserColumn($column, $destinationUser, $eventObj->mergedUserIDs))();
        }
    }
}
