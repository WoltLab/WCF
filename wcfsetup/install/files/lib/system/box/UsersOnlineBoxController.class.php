<?php
namespace wcf\system\box;
use wcf\data\user\online\UsersOnlineList;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Lists all users who are online.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.box
 * @category	Community Framework
 */
class UsersOnlineBoxController extends AbstractBoxController {
	/**
	 * @inheritDoc
	 */
	protected $supportedPositions = ['footerBoxes', 'sidebarLeft', 'sidebarRight'];
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return WCF::getLanguage()->get('wcf.user.usersOnline'); // @todo
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('UsersOnlineList');
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasLink() {
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		if (MODULE_USERS_ONLINE && WCF::getSession()->getPermission('user.profile.canViewUsersOnlineList')) {
			$usersOnlineList = new UsersOnlineList();
			$usersOnlineList->readStats();
			$usersOnlineList->checkRecord();
			$usersOnlineList->getConditionBuilder()->add('session.userID IS NOT NULL');
			$usersOnlineList->readObjects();
			
			if (count($usersOnlineList)) {
				if ($this->getBox()->position == 'footerBoxes') {
					$templateName = 'boxUsersOnline';
				}
				else {
					$templateName = 'boxUsersOnlineSidebar';
				}
				
				WCF::getTPL()->assign([
					'usersOnlineList' => $usersOnlineList
				]);
				
				$this->content = WCF::getTPL()->fetch($templateName);
			}
		}
	}
}
