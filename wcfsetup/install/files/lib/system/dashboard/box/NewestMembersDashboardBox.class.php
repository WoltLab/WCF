<?php
namespace wcf\system\dashboard\box;
use wcf\data\dashboard\box\DashboardBox;
use wcf\data\user\UserProfileCache;
use wcf\data\DatabaseObject;
use wcf\page\IPage;
use wcf\system\cache\builder\NewestMembersCacheBuilder;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows a list of the newest members.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class NewestMembersDashboardBox extends AbstractSidebarDashboardBox {
	/**
	 * ids of the newest members
	 * @var	array<integer>
	 */
	public $newestMemberIDs = array();
	
	/**
	 * @see	\wcf\system\dashboard\box\IDashboardBox::init()
	 */
	public function init(DashboardBox $box, IPage $page) {
		parent::init($box, $page);
		
		// get ids
		$this->newestMemberIDs = NewestMembersCacheBuilder::getInstance()->getData();
		if (!empty($this->newestMemberIDs)) {
			UserProfileCache::getInstance()->cacheUserIDs($this->newestMemberIDs);
		}
		
		$this->fetched();
	}
	
	/**
	 * @see	\wcf\system\dashboard\box\AbstractContentDashboardBox::render()
	 */
	protected function render() {
		if (empty($this->newestMemberIDs)) return '';
		
		if (MODULE_MEMBERS_LIST) {
			$this->titleLink = LinkHandler::getInstance()->getLink('MembersList', array(), 'sortField=registrationDate&sortOrder=DESC');
		}
		
		$newestMembers = UserProfileCache::getInstance()->getUserProfiles($this->newestMemberIDs);
		DatabaseObject::sort($newestMembers, 'registrationDate', 'DESC');
		
		WCF::getTPL()->assign(array(
			'newestMembers' => $newestMembers
		));
		return WCF::getTPL()->fetch('dashboardBoxNewestMembers');
	}
}
