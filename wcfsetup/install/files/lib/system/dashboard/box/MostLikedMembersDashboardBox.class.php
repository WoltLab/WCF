<?php
namespace wcf\system\dashboard\box;
use wcf\data\user\UserProfileList;
use wcf\system\cache\builder\MostLikedMembersCacheBuilder;
use wcf\system\WCF;

/**
 * Shows a list of the most liked members.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.like
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class MostLikedMembersDashboardBox extends AbstractSidebarDashboardBox {
	/**
	 * @see	wcf\system\dashboard\box\AbstractContentDashboardBox::render()
	 */
	protected function render() {
		// get ids
		$mostLikedMemberIDs = MostLikedMembersCacheBuilder::getInstance()->getData();
		if (empty($mostLikedMemberIDs)) return '';
		
		// get profile data
		$userProfileList = new UserProfileList();
		$userProfileList->sqlOrderBy = 'user_table.likesReceived DESC';
		$userProfileList->setObjectIDs($mostLikedMemberIDs);
		$userProfileList->readObjects();
		
		WCF::getTPL()->assign(array(
			'mostLikedMembers' => $userProfileList
		));
		return WCF::getTPL()->fetch('dashboardBoxMostLikedMembers');
	}
}
