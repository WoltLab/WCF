<?php
namespace wcf\system\dashboard\box;
use wcf\data\dashboard\box\DashboardBox;
use wcf\data\user\UserProfileCache;
use wcf\data\DatabaseObject;
use wcf\page\IPage;
use wcf\system\cache\builder\MostLikedMembersCacheBuilder;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows a list of the most liked members.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class MostLikedMembersDashboardBox extends AbstractSidebarDashboardBox {
	/**
	 * ids of the most liked members
	 * @var	array<integer>
	 */
	public $mostLikedMemberIDs = array();
	
	/**
	 * @see	\wcf\system\dashboard\box\IDashboardBox::init()
	 */
	public function init(DashboardBox $box, IPage $page) {
		parent::init($box, $page);
		
		// get ids
		$this->mostLikedMemberIDs = MostLikedMembersCacheBuilder::getInstance()->getData();
		
		$this->fetched();
		
		if (!empty($this->mostLikedMemberIDs)) {
			UserProfileCache::getInstance()->cacheUserIDs($this->mostLikedMemberIDs);
		}
	}
	
	/**
	 * @see	\wcf\system\dashboard\box\AbstractContentDashboardBox::render()
	 */
	protected function render() {
		if (empty($this->mostLikedMemberIDs)) return '';
		
		if (MODULE_MEMBERS_LIST) {
			$this->titleLink = LinkHandler::getInstance()->getLink('MembersList', array(), 'sortField=likesReceived&sortOrder=DESC');
		}
		
		$mostLikedMembers = UserProfileCache::getInstance()->getUserProfiles($this->mostLikedMemberIDs);
		DatabaseObject::sort($mostLikedMembers, 'likesReceived', 'DESC');
		
		WCF::getTPL()->assign(array(
			'mostLikedMembers' => $mostLikedMembers
		));
		return WCF::getTPL()->fetch('dashboardBoxMostLikedMembers');
	}
}
