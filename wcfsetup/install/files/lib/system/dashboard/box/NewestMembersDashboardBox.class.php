<?php
namespace wcf\system\dashboard\box;
use wcf\data\dashboard\box\DashboardBox;
use wcf\data\DatabaseObject;
use wcf\page\IPage;
use wcf\system\cache\builder\NewestMembersCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows a list of the newest members.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class NewestMembersDashboardBox extends AbstractSidebarDashboardBox {
	/**
	 * ids of the newest members
	 * @var	integer[]
	 */
	public $newestMemberIDs = [];
	
	/**
	 * @inheritDoc
	 */
	public function init(DashboardBox $box, IPage $page) {
		parent::init($box, $page);
		
		// get ids
		$this->newestMemberIDs = NewestMembersCacheBuilder::getInstance()->getData();
		if (!empty($this->newestMemberIDs)) {
			UserProfileRuntimeCache::getInstance()->cacheObjectIDs($this->newestMemberIDs);
		}
		
		$this->fetched();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function render() {
		if (empty($this->newestMemberIDs)) return '';
		
		if (MODULE_MEMBERS_LIST) {
			$this->titleLink = LinkHandler::getInstance()->getLink('MembersList', [], 'sortField=registrationDate&sortOrder=DESC');
		}
		
		$newestMembers = UserProfileRuntimeCache::getInstance()->getObjects($this->newestMemberIDs);
		DatabaseObject::sort($newestMembers, 'registrationDate', 'DESC');
		
		WCF::getTPL()->assign([
			'newestMembers' => $newestMembers
		]);
		return WCF::getTPL()->fetch('dashboardBoxNewestMembers');
	}
}
