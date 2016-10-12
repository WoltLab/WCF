<?php
namespace wcf\page;
use wcf\data\user\TeamList;
use wcf\system\page\PageLocationManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the team members list.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 * 
 * @property	TeamList	$objectList
 */
class TeamPage extends MultipleLinkPage {
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.profile.canViewMembersList'];
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_TEAM_PAGE'];
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = 1000;
	
	/**
	 * @inheritDoc
	 */
	public $sortField = MEMBERS_LIST_DEFAULT_SORT_FIELD;
	
	/**
	 * @inheritDoc
	 */
	public $sortOrder = MEMBERS_LIST_DEFAULT_SORT_ORDER;
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = TeamList::class;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->canonicalURL = LinkHandler::getInstance()->getLink('Team');
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// add breadcrumbs
		if (MODULE_MEMBERS_LIST) PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.MembersList');
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'allowSpidersToIndexThisPage' => true
		]);
	}
}
