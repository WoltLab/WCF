<?php
namespace wcf\system\dashboard\box;
use wcf\data\dashboard\box\DashboardBox;
use wcf\data\DatabaseObject;
use wcf\page\IPage;
use wcf\system\cache\builder\MostLikedMembersCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows a list of the most liked members.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class MostLikedMembersDashboardBox extends AbstractSidebarDashboardBox {
	/**
	 * ids of the most liked members
	 * @var	integer[]
	 */
	public $mostLikedMemberIDs = [];
	
	/**
	 * @inheritDoc
	 */
	public function init(DashboardBox $box, IPage $page) {
		parent::init($box, $page);
		
		// get ids
		$this->mostLikedMemberIDs = MostLikedMembersCacheBuilder::getInstance()->getData();
		
		$this->fetched();
		
		if (!empty($this->mostLikedMemberIDs)) {
			UserProfileRuntimeCache::getInstance()->cacheObjectIDs($this->mostLikedMemberIDs);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function render() {
		if (empty($this->mostLikedMemberIDs)) return '';
		
		if (MODULE_MEMBERS_LIST) {
			$this->titleLink = LinkHandler::getInstance()->getLink('MembersList', [], 'sortField=likesReceived&sortOrder=DESC');
		}
		
		$mostLikedMembers = UserProfileRuntimeCache::getInstance()->getObjects($this->mostLikedMemberIDs);
		DatabaseObject::sort($mostLikedMembers, 'likesReceived', 'DESC');
		
		WCF::getTPL()->assign([
			'mostLikedMembers' => $mostLikedMembers
		]);
		return WCF::getTPL()->fetch('dashboardBoxMostLikedMembers');
	}
}
