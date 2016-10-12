<?php
namespace wcf\data\acp\menu\item;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes ACP menu item-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acp\Menu\Item
 * 
 * @method	ACPMenuItem		create()
 * @method	ACPMenuItemEditor[]	getObjects()
 * @method	ACPMenuItemEditor	getSingleObject()
 */
class ACPMenuItemAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = ACPMenuItemEditor::class;
}
