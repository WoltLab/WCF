<?php
namespace wcf\page;
use wcf\system\menu\user\UserMenu;
use wcf\system\session\Session;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;

/**
 * Shows the account security page.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 * @since       5.4
 */
class AccountSecurityPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * @var Session[]
	 */
	private $activeSessions;
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
	
		$this->activeSessions = SessionHandler::getInstance()->getUserSessions(WCF::getUser());
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'activeSessions' => $this->activeSessions
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		// set active tab
		UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.profile.security');
		
		parent::show();
	}
}
