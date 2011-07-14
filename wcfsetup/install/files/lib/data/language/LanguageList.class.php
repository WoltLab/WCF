<?php
namespace wcf\data\language;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of languages.
 * 
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.language
 * @category 	Community Framework
 */
class LanguageList extends DatabaseObjectList {
	/**
	 * @see	DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\language\Language';
}
?>