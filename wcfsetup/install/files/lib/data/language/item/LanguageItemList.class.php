<?php
namespace wcf\data\language\item;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of language items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Language\Item
 *
 * @method	LanguageItem		current()
 * @method	LanguageItem[]		getObjects()
 * @method	LanguageItem|null	search($objectID)
 * @property	LanguageItem[]		$objects
 */
class LanguageItemList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = LanguageItem::class;
}
