<?php
namespace wcf\acp\page;
use wcf\data\user\rank\UserRankList;
use wcf\page\SortablePage;

/**
 * Lists available user ranks.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	UserRankList	$objectList
 */
class UserRankListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.rank.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.user.rank.canManageRank'];
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_USER_RANK'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = UserRankList::class;
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'rankTitle';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['rankID', 'groupID', 'requiredPoints', 'rankTitle', 'rankImage', 'requiredGender'];
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects = 'user_group.groupName';
		$this->objectList->sqlJoins = 'LEFT JOIN wcf'.WCF_N.'_user_group user_group ON (user_group.groupID = user_rank.groupID)';
	}
}
