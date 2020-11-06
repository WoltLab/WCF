<?php
namespace wcf\page;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\menu\user\UserMenu;
use wcf\system\session\Session;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;

/**
 * Shows the account security page.
 *
 * @author	Tim Duesterhus, Joshua Ruesweg
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
	 * @var ObjectType[]
	 */
	private $multifactorMethods;
	
	/**
	 * @var int[]
	 */
	private $enabledMultifactorMethods;
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
	
		$this->activeSessions = SessionHandler::getInstance()->getUserSessions(WCF::getUser());
		
		usort($this->activeSessions, function ($a, $b) {
			return $b->getLastActivityTime() <=> $a->getLastActivityTime();
		});
		
		$this->multifactorMethods = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.multifactor');
		
		usort($this->multifactorMethods, function (ObjectType $a, ObjectType $b) {
			return $b->priority <=> $a->priority;
		});
		
		$this->enabledMultifactorMethods = array_flip(array_map(function (ObjectType $o) {
			return $o->objectTypeID;
		}, WCF::getUser()->getEnabledMultifactorMethods()));
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'activeSessions' => $this->activeSessions,
			'multifactorMethods' => $this->multifactorMethods,
			'enabledMultifactorMethods' => $this->enabledMultifactorMethods,
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
