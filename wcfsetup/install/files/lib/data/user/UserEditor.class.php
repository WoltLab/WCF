<?php
namespace wcf\data\user;
use wcf\data\user\group\UserGroup;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;
use wcf\util\PasswordUtil;
use wcf\util\StringUtil;

/**
 * Provides functions to edit users.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User
 * 
 * @method	User	getDecoratedObject()
 * @mixin	User
 */
class UserEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = User::class;
	
	/**
	 * list of user options default values
	 * @var	array
	 */
	protected static $userOptionDefaultValues = null;
	
	/**
	 * @inheritDoc
	 */
	public static function create(array $parameters = []) {
		// create salt and password hash
		if ($parameters['password'] !== '') {
			$parameters['password'] = PasswordUtil::getDoubleSaltedHash($parameters['password']);
		}
		
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
	 * @inheritDoc
	 */
	public static function deleteAll(array $objectIDs = []) {
		// unmark users
		ClipboardHandler::getInstance()->unmark($objectIDs, ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user'));
		
		return parent::deleteAll($objectIDs);
	}
	
	/**
	 * @inheritDoc
	 */
	public function update(array $parameters = []) {
		// update salt and create new password hash
		if (isset($parameters['password']) && $parameters['password'] !== '') {
			$parameters['password'] = PasswordUtil::getDoubleSaltedHash($parameters['password']);
			$parameters['accessToken'] = StringUtil::getRandomID();
			
			// update accessToken
			$this->accessToken = $parameters['accessToken'];
		}
		else {
			unset($parameters['password'], $parameters['accessToken']);
		}
		
		parent::update($parameters);
	}
	
	/**
	 * Inserts default options.
	 * 
	 * @param	integer		$userID
	 */
	protected static function createUserOptions($userID) {
		// fetch default values
		if (self::$userOptionDefaultValues === null) {
			self::$userOptionDefaultValues = [];
			
			$sql = "SELECT	optionID, defaultValue
				FROM	wcf".WCF_N."_user_option";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			while ($row = $statement->fetchArray()) {
				if (!empty($row['defaultValue'])) {
					self::$userOptionDefaultValues[$row['optionID']] = $row['defaultValue'];
				}
			}
		}
		
		// insert default values
		$keys = $values = '';
		$statementParameters = [$userID];
		foreach (self::$userOptionDefaultValues as $optionID => $optionValue) {
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
	public function updateUserOptions(array $userOptions = []) {
		$updateSQL = '';
		$statementParameters = [];
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
	 * @param	array		$groupIDs
	 * @param	boolean		$deleteOldGroups
	 * @param	boolean		$addDefaultGroups
	 */
	public function addToGroups(array $groupIDs, $deleteOldGroups = true, $addDefaultGroups = true) {
		// add default groups
		if ($addDefaultGroups) {
			$groupIDs = array_merge($groupIDs, UserGroup::getGroupIDsByType([UserGroup::EVERYONE, UserGroup::USERS]));
			$groupIDs = array_unique($groupIDs);
		}
		
		// remove old groups
		if ($deleteOldGroups) {
			$sql = "DELETE FROM	wcf".WCF_N."_user_to_group
				WHERE		userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->userID]);
		}
		
		// insert new groups
		if (!empty($groupIDs)) {
			$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_to_group
							(userID, groupID)
				VALUES			(?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($groupIDs as $groupID) {
				$statement->execute([$this->userID, $groupID]);
			}
		}
	}
	
	/**
	 * Adds a user to a user group.
	 * 
	 * @param	integer	$groupID
	 */
	public function addToGroup($groupID) {
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_to_group
						(userID, groupID)
			VALUES			(?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->userID, $groupID]);
	}
	
	/**
	 * Removes a user from a user group.
	 * 
	 * @param	integer		$groupID
	 */
	public function removeFromGroup($groupID) {
		$sql = "DELETE FROM	wcf".WCF_N."_user_to_group
			WHERE		userID = ?
					AND groupID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->userID, $groupID]);
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
			$statement->execute([
				$this->userID,
				$groupID
			]);
		}
	}
	
	/**
	 * Saves the visible languages of a user.
	 * 
	 * @param	array		$languageIDs
	 * @param	boolean		$deleteOldLanguages
	 */
	public function addToLanguages(array $languageIDs, $deleteOldLanguages = true) {
		// remove previous languages
		if ($deleteOldLanguages) {
			$sql = "DELETE FROM	wcf".WCF_N."_user_to_language
				WHERE		userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->userID]);
		}
		
		// insert language ids
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_to_language
						(userID, languageID)
			VALUES			(?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		if (!empty($languageIDs)) {
			WCF::getDB()->beginTransaction();
			foreach ($languageIDs as $languageID) {
				$statement->execute([
					$this->userID,
					$languageID
				]);
			}
			WCF::getDB()->commitTransaction();
		}
		else {
			// no language id given, use default language id instead
			$statement->execute([
				$this->userID,
				LanguageFactory::getInstance()->getDefaultLanguageID()
			]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		SessionHandler::resetSessions();
	}
}
