<?php
namespace wcf\data\style\variable;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of style variables.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.style.variable
 * @category	Community Framework
 */
class StyleVariableList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\style\variable\StyleVariable';
}
