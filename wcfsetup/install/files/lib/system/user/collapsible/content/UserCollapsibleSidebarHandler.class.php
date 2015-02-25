<?php
namespace wcf\system\user\collapsible\content;
use wcf\system\exception\UserInputException;
use wcf\system\IAJAXInvokeAction;
use wcf\system\SingletonFactory;
use wcf\util\StringUtil;

/**
 * Provides methods for handling collapsible sidebars.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.collapsible.content
 * @category	Community Framework
 */
class UserCollapsibleSidebarHandler extends SingletonFactory implements IAJAXInvokeAction {
	/**
	 * list of methods allowed for remote invoke
	 * @var	array<string>
	 */
	public static $allowInvoke = array('toggle');
	
	/**
	 * Toggles a sidebar.
	 */
	public function toggle() {
		$isOpen = (isset($_POST['isOpen'])) ? intval($_POST['isOpen']) : 1;
		$objectID = (isset($_POST['sidebarName'])) ? StringUtil::trim($_POST['sidebarName']) : '';
		if (empty($objectID)) {
			throw new UserInputException('sidebarName');
		}
		
		$objectTypeID = UserCollapsibleContentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.collapsibleSidebar');
		if ($isOpen) {
			UserCollapsibleContentHandler::getInstance()->markAsOpened($objectTypeID, $objectID);
		}
		else {
			UserCollapsibleContentHandler::getInstance()->markAsCollapsed($objectTypeID, $objectID);
		}
	}
}
