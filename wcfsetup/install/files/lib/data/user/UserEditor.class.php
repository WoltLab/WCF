<?php
namespace wcf\data\user;
use wcf\data\user\group\UserGroup;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Provides functions to edit users.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user
 * @category	Community Framework
 */
class UserEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\User';
	
	/**
	 * @see	wcf\data\IEditableObject::create()
	 */
	public static function create(array $parameters = array()) {
		// create salt and password hash
		$parameters['salt'] = StringUtil::getRandomID();
		$parameters['password'] = StringUtil::getDoubleSaltedHash($parameters['password'], $parameters['salt']);
		
		// create accessToken for AbstractAuthedPage
		$parameters['accessToken'] = StringUtil::getRandomID();
		
		// handle registration date
		if (!isset($parameters['registrationDate'])) $parameters['registrationDate'] = TIME_NOW;
		
		$user = parent::create($parameters);
		
		// create default values for user options
		self::createUserOptions($user->userID);
		
		return $user;
	}
	
	/**
	 * @see	wcf\data\IEditableObject::deleteAll()
	 */
	public static function deleteAll(array $objectIDs = array()) {
		// unmark users
		ClipboardHandler::getInstance()->unmark($objectIDs, ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user'));
	
		return parent::deleteAll($objectIDs);
	}
	
	/**
	 * @see	wcf\data\DatabaseObjectEditor::update()
	 */
	public function update(array $parameters = array()) {
		// update salt and create new password hash
		if (isset($parameters['password'])) {
			$parameters['salt'] = StringUtil::getRandomID();
			$parameters['password'] = StringUtil::getDoubleSaltedHash($parameters['password'], $parameters['salt']);
			
			$parameters['accessToken'] = StringUtil::getRandomID();
			
			// update salt and accessToken
			$this->salt = $parameters['salt'];
			$this->accessToken = $parameters['accessToken'];
		}
		
		parent::update($parameters);
	}
	
	/**
	 * Inserts default options.
	 * 
	 * @param	integer		$userID
	 */
	protected static function createUserOptions($userID) {
		$userOptions = array();
		
		// fetch default values
		$sql = "SELECT	optionID, defaultValue
			FROM	wcf".WCF_N."_user_option";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			if (!empty($row['defaultValue'])) {
				$userOptions[$row['optionID']] = $row['defaultValue'];
			}
		}
		
		// insert default values
		$keys = $values = '';
		$statementParameters = array($userID);
		foreach ($userOptions as $optionID => $optionValue) {
			$keys .= ', userOption'.$optionID;
			$values .= ', ?';
			$statementParameters[] = $optionValue;
		}
		
		$sql = "INSERT INTO	wcf".WCF_N."_user_option_value
					(userID".$keys.")
			VALUES		(?".$values.")";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($statementParameters);
	}
	
	/**
	 * Updates user options.
	 * 
	 * @param	array		$userOptions
	 */
	public function updateUserOptions(array $userOptions = array()) {
		$updateSQL = '';
		$statementParameters = array();
		foreach ($userOptions as $optionID => $optionValue) {
			if (!empty($updateSQL)) $updateSQL .= ',';
			
			$updateSQL .= 'userOption'.$optionID.' = ?';
			$statementParameters[] = $optionValue;
		}
		$statementParameters[] = $this->userID;
		
		if (!empty($updateSQL)) {
			$sql = "UPDATE	wcf".WCF_N."_user_option_value
				SET	".$updateSQL."
				WHERE	userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($statementParameters);
		}
	}
	
	/**
	 * Adds a user to the groups he should be in.
	 * 
	 * @param 	array 		$groups
	 * @param	boolean		$deleteOldGroups
	 * @param 	boolean 	$addDefaultGroups
	 */
	public function addToGroups(array $groupIDs, $deleteOldGroups = true, $addDefaultGroups = true) {
		// add default groups
		if ($addDefaultGroups) {
			$groupIDs = array_merge($groupIDs, UserGroup::getGroupIDsByType(array(UserGroup::EVERYONE, UserGroup::USERS)));
			$groupIDs = array_unique($groupIDs);
		}
		
		// remove old groups
		if ($deleteOldGroups) {
			$sql = "DELETE FROM	wcf".WCF_N."_user_to_group
				WHERE		userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->userID));
		}
		
		// insert new groups
		if (!empty($groupIDs)) {
			$sql = "INSERT INTO	wcf".WCF_N."_user_to_group
						(userID, groupID)
				VALUES		(?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($groupIDs as $groupID) {
				$statement->execute(array($this->userID, $groupID));
			}
		}
	}
	
	/**
	 * Adds a user to a user group.
	 * 
	 * @param 	integer 	$groupID
	 */
	public function addToGroup($groupID) {
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_user_to_group
			WHERE	userID = ?
				AND groupID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->userID,
			$groupID
		));
		$row = $statement->fetchArray();
		
		if (!$row['count']) {
			$sql = "INSERT INTO	wcf".WCF_N."_user_to_group
						(userID, groupID)
				VALUES		(?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->userID, $groupID));
		}
	}
	
	/**
	 * Removes a user from a user group.
	 * 
	 * @param 	integer 	$groupID
	 */
	public function removeFromGroup($groupID) {
		$sql = "DELETE FROM	wcf".WCF_N."_user_to_group
			WHERE		userID = ?
					AND groupID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->userID, $groupID));
	}
	
	/**
	 * Removes a user from multiple user groups.
	 * 
	 * @param	array		$groupIDs
	 */
	public function removeFromGroups(array $groupIDs) {
		$sql = "DELETE FROM	wcf".WCF_N."_user_to_group
			WHERE		userID = ?
					AND groupID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($groupIDs as $groupID) {
			$statement->execute(array(
				$this->userID,
				$groupID
			));
		}
	}
	
	/**
	 * Saves the visible languages of a user.
	 * 
	 * @param 	array 		$languageIDs
	 */
	public function addToLanguages(array $languageIDs) {
		// remove previous languages
		$sql = "DELETE FROM	wcf".WCF_N."_user_to_language
			WHERE		userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->userID));
		
		// insert language ids
		$sql = "INSERT INTO	wcf".WCF_N."_user_to_language
					(userID, languageID)
			VALUES		(?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		if (!empty($languageIDs)) {
			WCF::getDB()->beginTransaction();
			foreach ($languageIDs as $languageID) {
				$statement->execute(array(
					$this->userID,
					$languageID
				));
			}
			WCF::getDB()->commitTransaction();
		}
		else {
			// no language id given, use default language id instead
			$statement->execute(array(
				$this->userID,
				LanguageFactory::getInstance()->getDefaultLanguageID()
			));
		}
	}
	
	/**
	 * @see	wcf\data\IEditableCachedObject::resetCache()
	 */
	public static function resetCache() {
		SessionHandler::resetSessions();
	}
}
