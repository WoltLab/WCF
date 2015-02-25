<?php
namespace wcf\data\core\object;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of core objects.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.event
 * @category	Community Framework
 */
class CoreObjectList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\core\object\CoreObject';
}
