<?php
namespace wcf\system\dashboard\box;
use wcf\data\dashboard\box\DashboardBox;
use wcf\data\user\UserProfileCache;
use wcf\data\DatabaseObject;
use wcf\page\IPage;
use wcf\system\cache\builder\MostActiveMembersCacheBuilder;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows a list of the most active members.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class MostActiveMembersDashboardBox extends AbstractSidebarDashboardBox {
	/**
	 * ids of the most active members
	 * @var	array<integer>
	 */
	public $mostActiveMemberIDs = array();
	
	/**
	 * @see	\wcf\system\dashboard\box\AbstractDashboardBoxContent::init()
	 */
	public function init(DashboardBox $box, IPage $page) {
		parent::init($box, $page);
		
		// get ids
		$this->mostActiveMemberIDs = MostActiveMembersCacheBuilder::getInstance()->getData();
		if (!empty($this->mostActiveMemberIDs)) {
			UserProfileCache::getInstance()->cacheUserIDs($this->mostActiveMemberIDs);
		}
		
		$this->fetched();
	}
	
	/**
	 * @see	\wcf\system\dashboard\box\AbstractContentDashboardBox::render()
	 */
	protected function render() {
		if (empty($this->mostActiveMemberIDs)) return '';
		
		if (MODULE_MEMBERS_LIST) {
			$this->titleLink = LinkHandler::getInstance()->getLink('MembersList', array(), 'sortField=activityPoints&sortOrder=DESC');
		}
		
		$mostActiveMembers = UserProfileCache::getInstance()->getUserProfiles($this->mostActiveMemberIDs);
		DatabaseObject::sort($mostActiveMembers, 'activityPoints', 'DESC');
		
		WCF::getTPL()->assign(array(
			'mostActiveMembers' => $mostActiveMembers
		));
		return WCF::getTPL()->fetch('dashboardBoxMostActiveMembers');
	}
}
