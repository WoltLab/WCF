<?php
namespace wcf\system\dashboard\box;
use wcf\data\user\UserProfileList;
use wcf\system\cache\builder\NewestMembersCacheBuilder;
use wcf\system\WCF;

/**
 * Shows a list of the newest members.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class NewestMembersDashboardBox extends AbstractSidebarDashboardBox {
	/**
	 * @see	wcf\system\dashboard\box\AbstractContentDashboardBox::render()
	 */
	protected function render() {
		// get ids
		$newestMemberIDs = NewestMembersCacheBuilder::getInstance()->getData();
		if (empty($newestMemberIDs)) return '';
		
		// get profile data
		$userProfileList = new UserProfileList();
		$userProfileList->sqlOrderBy = 'user_table.registrationDate DESC';
		$userProfileList->setObjectIDs($newestMemberIDs);
		$userProfileList->readObjects();
		
		WCF::getTPL()->assign(array(
			'newestMembers' => $userProfileList
		));
		return WCF::getTPL()->fetch('dashboardBoxNewestMembers');
	}
}
