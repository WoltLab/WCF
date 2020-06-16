<?php
namespace wcf\system\importer;
use wcf\data\user\trophy\UserTrophy;
use wcf\data\user\trophy\UserTrophyEditor;

/**
 * Represents a user trophy importer.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 * @since	3.1
 */
class UserTrophyImporter extends AbstractImporter {
	/**
	 * @inheritDoc
	 */
	protected $className = UserTrophy::class;
	
	/**
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
		if (isset($data['trophyID'])) {
			$data['trophyID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.trophy', $data['trophyID']);
		}
		
		if (isset($data['userID'])) {
			$data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
		}
		
		if (!$data['userID'] || !$data['trophyID']) {
			return 0;
		}
		
		$userTrophy = UserTrophyEditor::create($data);
		
		if (isset($additionalData['i18n']['description'])) {
			$updateData['description'] = 'wcf.user.trophy.description' . $userTrophy->userTrophyID;
			
			$items = [];
			foreach ($additionalData['i18n']['description'] as $languageID => $languageItemValue) {
				$items[] = [
					'languageID' => $languageID,
					'languageItem' => 'wcf.user.trophy.description' . $userTrophy->userTrophyID,
					'languageItemValue' => $languageItemValue
				];
			}
			
			$this->importI18nValues($items, 'wcf.user.trophy', 'com.woltlab.wcf');
			
			(new UserTrophyEditor($userTrophy))->update($updateData);
		}
		
		ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.userTrophy', $oldID, $userTrophy->getObjectID());
		
		return $userTrophy->getObjectID();
	}
}
