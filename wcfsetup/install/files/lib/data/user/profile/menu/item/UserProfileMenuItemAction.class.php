<?php
namespace wcf\data\user\profile\menu\item;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\menu\user\profile\UserProfileMenu;

/**
 * Executes user profile menu item-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.profile.menu.item
 * @category	Community Framework
 */
class UserProfileMenuItemAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	protected $allowGuestAccess = array('getContent');
	
	/**
	 * menu item
	 * @var	\wcf\data\user\profile\menu\item\UserProfileMenuItem
	 */
	protected $menuItem = null;
	
	/**
	 * Validates menu item.
	 */
	public function validateGetContent() {
		$this->readString('menuItem', false, 'data');
		$this->readInteger('userID', false, 'data');
		$this->readString('containerID', false, 'data');
		
		$this->menuItem = UserProfileMenu::getInstance()->getMenuItem($this->parameters['data']['menuItem']);
		if ($this->menuItem === null) {
			throw new UserInputException('menuItem');
		}
		if (!$this->menuItem->getContentManager()->isVisible($this->parameters['data']['userID'])) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Returns content for given menu item.
	 */
	public function getContent() {
		$contentManager = $this->menuItem->getContentManager();
		
		return array(
			'containerID' => $this->parameters['data']['containerID'],
			'template' => $contentManager->getContent($this->parameters['data']['userID'])
		);
	}
}
