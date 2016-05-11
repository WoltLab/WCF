<?php
namespace wcf\data\acp\menu\item;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit ACP menu items.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.menu.item
 * @category	Community Framework
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
