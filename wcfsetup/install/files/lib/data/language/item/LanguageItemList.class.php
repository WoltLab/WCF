<?php
namespace wcf\data\language\item;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of language items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.language.item
 * @category	Community Framework
 */
class LanguageItemList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\language\item\LanguageItem';
}
