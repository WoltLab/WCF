<?php
namespace wcf\system\importer;
use wcf\data\user\group\UserGroup;
use wcf\data\user\rank\UserRankEditor;

/**
 * Imports user ranks.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class UserRankImporter extends AbstractImporter {
	/**
	 * @see	\wcf\system\importer\AbstractImporter::$className
	 */
	protected $className = 'wcf\data\user\rank\UserRank';
	
	/**
	 * @see	\wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		$data['groupID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user.group', $data['groupID']);
		if (!$data['groupID']) $data['groupID'] = UserGroup::getGroupByType(UserGroup::USERS)->groupID;
		
		$rank = UserRankEditor::create($data);
		
		ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.user.rank', $oldID, $rank->rankID);
		
		return $rank->rankID;
	}
}
