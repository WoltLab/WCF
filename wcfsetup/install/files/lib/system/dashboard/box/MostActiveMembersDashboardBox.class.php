<?php
namespace wcf\system\dashboard\box;
use wcf\data\user\UserProfileList;
use wcf\system\cache\builder\MostActiveMembersCacheBuilder;
use wcf\system\WCF;

/**
 * Shows a list of the most active members.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class MostActiveMembersDashboardBox extends AbstractSidebarDashboardBox {
	/**
	 * @see	wcf\system\dashboard\box\AbstractContentDashboardBox::render()
	 */
	protected function render() {
		// get ids
		$mostActiveMemberIDs = MostActiveMembersCacheBuilder::getInstance()->getData();
		if (empty($mostActiveMemberIDs)) return '';
		
		// get profile data
		$userProfileList = new UserProfileList();
		$userProfileList->sqlOrderBy = 'user_table.activityPoints DESC';
		$userProfileList->setObjectIDs($mostActiveMemberIDs);
		$userProfileList->readObjects();
		
		WCF::getTPL()->assign(array(
			'mostActiveMembers' => $userProfileList
		));
		return WCF::getTPL()->fetch('dashboardBoxMostActiveMembers');
	}
}
