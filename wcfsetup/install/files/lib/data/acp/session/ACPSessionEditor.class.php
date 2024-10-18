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
 * @method  ACPSession  getDecoratedObject()
 * @mixin   ACPSession
 * @deprecated  5.4 Distinct ACP sessions have been removed. This class is preserved due to its use in legacy sessions.
 */
class ACPSessionEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = ACPSession::class;

    /**
     * @inheritDoc
     * @return  ACPSession
     */
    public static function create(array $parameters = [])
    {
        if (isset($parameters['userID']) && !$parameters['userID']) {
            $parameters['userID'] = null;
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return parent::create($parameters);
    }

    /**
     * @inheritDoc
     */
    public function update(array $parameters = [])
    {
        if (isset($parameters['userID']) && !$parameters['userID']) {
            $parameters['userID'] = null;
        }

        parent::update($parameters);
    }

    /**
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
     * @deprecated 5.4 - Sessions are managed via the SessionHandler.
     */
    public static function deleteExpiredSessions($timestamp)
    {
        SessionHandler::getInstance()->prune();
    }
}
