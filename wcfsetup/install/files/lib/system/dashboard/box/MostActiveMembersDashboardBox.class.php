<?php
namespace wcf\system\dashboard\box;
use wcf\data\dashboard\box\DashboardBox;
use wcf\data\DatabaseObject;
use wcf\page\IPage;
use wcf\system\cache\builder\MostActiveMembersCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows a list of the most active members.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class MostActiveMembersDashboardBox extends AbstractSidebarDashboardBox {
	/**
	 * ids of the most active members
	 * @var	integer[]
	 */
	public $mostActiveMemberIDs = [];
	
	/**
	 * @inheritDoc
	 */
	public function init(DashboardBox $box, IPage $page) {
		parent::init($box, $page);
		
		// get ids
		$this->mostActiveMemberIDs = MostActiveMembersCacheBuilder::getInstance()->getData();
		if (!empty($this->mostActiveMemberIDs)) {
			UserProfileRuntimeCache::getInstance()->cacheObjectIDs($this->mostActiveMemberIDs);
		}
		
		$this->fetched();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function render() {
		if (empty($this->mostActiveMemberIDs)) return '';
		
		if (MODULE_MEMBERS_LIST) {
			$this->titleLink = LinkHandler::getInstance()->getLink('MembersList', [], 'sortField=activityPoints&sortOrder=DESC');
		}
		
		$mostActiveMembers = UserProfileRuntimeCache::getInstance()->getObjects($this->mostActiveMemberIDs);
		DatabaseObject::sort($mostActiveMembers, 'activityPoints', 'DESC');
		
		WCF::getTPL()->assign([
			'mostActiveMembers' => $mostActiveMembers
		]);
		return WCF::getTPL()->fetch('dashboardBoxMostActiveMembers');
	}
}
