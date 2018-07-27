<?php
namespace wcf\data\acp\menu\item;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\ACPMenuCacheBuilder;

/**
 * Provides functions to edit ACP menu items.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acp\Menu\Item
 * 
 * @method static	ACPMenuItem	create(array $parameters = [])
 * @method		ACPMenuItem	getDecoratedObject()
 * @mixin		ACPMenuItem
 */
class ACPMenuItemEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ACPMenuItem::class;
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		ACPMenuCacheBuilder::getInstance()->reset();
	}
}
