<?php
namespace wcf\system\importer;
use wcf\data\user\group\UserGroup;
use wcf\data\user\rank\UserRank;
use wcf\data\user\rank\UserRankEditor;

/**
 * Imports user ranks.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
class UserRankImporter extends AbstractImporter {
	/**
	 * @inheritDoc
	 */
	protected $className = UserRank::class;
	
	/**
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
		$data['groupID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user.group', $data['groupID']);
		if (!$data['groupID']) $data['groupID'] = UserGroup::getGroupByType(UserGroup::USERS)->groupID;
		
		$rank = UserRankEditor::create($data);
		
		ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.user.rank', $oldID, $rank->rankID);
		
		return $rank->rankID;
	}
}
