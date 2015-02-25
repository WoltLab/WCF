<?php
namespace wcf\acp\page;
use wcf\page\SortablePage;

/**
 * Lists available user ranks.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class UserRankListPage extends SortablePage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.rank.list';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.rank.canManageRank');
	
	/**
	 * @see	wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_USER_RANK');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\user\rank\UserRankList';
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$defaultSortField
	 */
	public $defaultSortField = 'rankTitle';
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$validSortFields
	 */
	public $validSortFields = array('rankID', 'groupID', 'requiredPoints', 'rankTitle', 'rankImage', 'requiredGender');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::show()
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects = 'user_group.groupName';
		$this->objectList->sqlJoins = 'LEFT JOIN wcf'.WCF_N.'_user_group user_group ON (user_group.groupID = user_rank.groupID)';
	}
}
