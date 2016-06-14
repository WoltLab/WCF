<?php
namespace wcf\data\acp\menu\item;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit ACP menu items.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acp\Menu\Item
 * 
 * @method	ACPMenuItem	getDecoratedObject()
 * @mixin	ACPMenuItem
 */
class ACPMenuItemEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ACPMenuItem::class;
}
