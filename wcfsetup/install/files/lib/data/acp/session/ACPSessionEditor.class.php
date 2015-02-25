<?php
namespace wcf\data\acp\session;
use wcf\data\DatabaseObjectEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Provides functions to edit ACP sessions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.session
 * @category	Community Framework
 */
class ACPSessionEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\acp\session\ACPSession';
	
	/**
	 * @see	\wcf\data\DatabaseObjectEditor::create()
	 */
	public static function create(array $parameters = array()) {
		if (isset($parameters['userID']) && !$parameters['userID']) {
			$parameters['userID'] = null;
		}
		
		return parent::create($parameters);
	}
	
	/**
	 * @see	\wcf\data\DatabaseObjectEditor::create()
	 */
	public function update(array $parameters = array()) {
		if (isset($parameters['userID']) && !$parameters['userID']) {
			$parameters['userID'] = null;
		}
		
		return parent::update($parameters);
	}
	
	/**
	 * Deletes active sessions of the given users.
	 * 
	 * @param	array<integer>	$userIDs
	 */
	public static function deleteUserSessions(array $userIDs = array()) {
		$conditionBuilder = new PreparedStatementConditionBuilder();
		if (!empty($userIDs)) {
			$conditionBuilder->add('userID IN (?)', array($userIDs));
		}
		
		$sql = "DELETE FROM	".call_user_func(array(static::$baseClass, 'getDatabaseTableName'))."
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
		$sql = "DELETE FROM	".call_user_func(array(static::$baseClass, 'getDatabaseTableName'))."
			WHERE		lastActivityTime < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($timestamp));
	}
}
