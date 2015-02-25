<?php
namespace wcf\data\acp\menu\item;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes ACP menu item-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.menu.item
 * @category	Community Framework
 */
class ACPMenuItemAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\acp\menu\item\ACPMenuItemEditor';
}
