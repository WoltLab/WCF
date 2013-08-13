<?php
namespace wcf\system\importer;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\database\DatabaseException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Imports users.
 *
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class UserImporter extends AbstractImporter {
	/**
	 * @see wcf\system\importer\AbstractImporter::$className
	 */
	protected $className = 'wcf\data\user\User';
	
	/**
	 * @see wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		// resolve duplicates
		$existingUser = User::getUserByUsername($data['username']);
		if ($existingUser->userID) {
			if (ImportHandler::getInstance()->getUserMergeMode() == 1 || (ImportHandler::getInstance()->getUserMergeMode() == 3 && StringUtil::toLowerCase($existingUser->email) != StringUtil::toLowerCase($data['email']))) {
				// rename user
				$data['username'] = self::resolveDuplicate($data['username']);
			}
			else {
				// merge user
				ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.user', $oldID, $existingUser->userID);
				
				return 0;
			}
		}
		
		// check existing user id
		if (is_numeric($oldID)) {
			$user = new User($oldID);
			if (!$user->userID) $data['userID'] = $oldID;
		}
		
		// get group ids
		$groupIDs = array();
		if (isset($additionalData['groupIDs'])) {
			foreach ($additionalData['groupIDs'] as $oldGroupID) {
				$newGroupID = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user.group', $oldGroupID);
				if ($newGroupID) $groupIDs[] = $newGroupID;
			}
		}
		
		// handle user options
		$userOptions = array();
		if (isset($additionalData['options'])) {
			foreach ($additionalData['options'] as $optionName => $optionValue) {
				if (is_int($optionName)) $optionID = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user.option', $optionName);
				else $optionID = User::getUserOptionID($optionName);
				
				if ($optionID) {
					$userOptions[$optionID] = $optionValue;
				}
			}
		}
		
		// handle languages
		$languageIDs = array();
		if (isset($additionalData['languages'])) {
			foreach ($additionalData['languages'] as $languageCode) {
				$language = LanguageFactory::getInstance()->getLanguageByCode($languageCode);
				if ($language !== null) $languageIDs[] = $language->languageID;
			}
		}
		
		// create user
		$action = new UserAction(array(), 'create', array(
			'data' => $data,
			'groups' => $groupIDs,
			'options' => $userOptions,
			'languages' => $languageIDs
		));
		$returnValues = $action->executeAction();
		
		$newUserID = $returnValues['returnValues']->userID;
		ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.user', $oldID, $newUserID);
		
		return $newUserID;
	}
	
	/**
	 * Revolves duplicate user names.
	 *
	 * @param	string		$username
	 * @return 	string		new username
	 */
	private static function resolveDuplicate($username) {
		$i = 0;
		$newUsername = '';
		do {
			$i++;
			$newUsername = 'Duplicate'.($i > 1 ? $i : '').' '.$username;
			// try username
			$sql = "SELECT	userID
				FROM	wcf".WCF_N."_user
				WHERE	username = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($newUsername));
			$row = $statement->fetchArray();
			if (empty($row['userID'])) break;
		}
		while (true);
	
		return $newUsername;
	}
}
