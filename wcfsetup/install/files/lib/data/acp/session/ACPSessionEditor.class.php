<?php
namespace wcf\data\acp\session;
use wcf\data\DatabaseObjectEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Provides functions to edit ACP sessions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acp\Session
 * 
 * @method	ACPSession	getDecoratedObject()
 * @mixin	ACPSession
 */
class ACPSessionEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ACPSession::class;
	
	/**
	 * @inheritDoc
	 */
	public static function create(array $parameters = []) {
		if (isset($parameters['userID']) && !$parameters['userID']) {
			$parameters['userID'] = null;
		}
		
		return parent::create($parameters);
	}
	
	/**
	 * @inheritDoc
	 */
	public function update(array $parameters = []) {
		if (isset($parameters['userID']) && !$parameters['userID']) {
			$parameters['userID'] = null;
		}
		
		parent::update($parameters);
	}
	
	/**
	 * Deletes active sessions of the given users.
	 * 
	 * @param	integer[]	$userIDs
	 */
	public static function deleteUserSessions(array $userIDs = []) {
		$conditionBuilder = new PreparedStatementConditionBuilder();
		if (!empty($userIDs)) {
			$conditionBuilder->add('userID IN (?)', [$userIDs]);
		}
		
		$sql = "DELETE FROM	".call_user_func([static::$baseClass, 'getDatabaseTableName'])."
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
	}
	
	/**
	 * Deletes the expired sessions.
	 * 
	 * @param	integer		$timestamp
	 */
	public static function deleteExpiredSessions($timestamp) {
		$sql = "DELETE FROM	".call_user_func([static::$baseClass, 'getDatabaseTableName'])."
			WHERE		lastActivityTime < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$timestamp]);
	}
}
