<?php
namespace wcf\data\language\category;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of language categories.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.language.category
 * @category	Community Framework
 */
class LanguageCategoryList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\language\category\LanguageCategory';
}
