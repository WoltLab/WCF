<?php
namespace wcf\data\acp\menu\item;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of ACP menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.menu.item
 * @category	Community Framework
 *
 * @method	ACPMenuItem		current()
 * @method	ACPMenuItem[]		getObjects()
 * @method	ACPMenuItem|null	search($objectID)
 * @property	ACPMenuItem[]		$objects
 */
class ACPMenuItemList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = ACPMenuItem::class;
}
