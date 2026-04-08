<?php

namespace wcf\data\acp\session;

use wcf\data\DatabaseObjectEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;

/**
 * Provides functions to edit ACP sessions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin ACPSession
 * @extends DatabaseObjectEditor<ACPSession>
 * @deprecated 5.4 Distinct ACP sessions have been removed. This class is preserved due to its use in legacy sessions.
 */
class ACPSessionEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = ACPSession::class;

    #[\Override]
    public static function create(array $parameters = [])
    {
        if (isset($parameters['userID']) && !$parameters['userID']) {
            $parameters['userID'] = null;
        }

        return parent::create($parameters);
    }

    #[\Override]
    public function update(array $parameters = [])
    {
        if (isset($parameters['userID']) && !$parameters['userID']) {
            $parameters['userID'] = null;
        }

        parent::update($parameters);
    }

    /**
     * @param int[] $userIDs
     * @return void
     * @deprecated 5.4 - Sessions are managed via the SessionHandler.
     */
    public static function deleteUserSessions(array $userIDs = [])
    {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        if (!empty($userIDs)) {
            $conditionBuilder->add('userID IN (?)', [$userIDs]);
        }

        $sql = "DELETE FROM " . \call_user_func([static::$baseClass, 'getDatabaseTableName']) . "
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());
    }

    /**
     * @return void
     * @deprecated 5.4 - Sessions are managed via the SessionHandler.
     */
    public static function deleteExpiredSessions(int $timestamp)
    {
        SessionHandler::getInstance()->prune();
    }
}
