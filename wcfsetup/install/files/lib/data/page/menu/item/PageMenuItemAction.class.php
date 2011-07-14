<?php
namespace wcf\data\page\menu\item;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes page menu item-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page.menu.item
 * @category 	Community Framework
 */
class PageMenuItemAction extends AbstractDatabaseObjectAction {
	/**
	 * @see AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\page\menu\item\PageMenuItemEditor';
}
?>