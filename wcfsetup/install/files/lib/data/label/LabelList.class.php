<?php
namespace wcf\data\label;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of labels.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.label
 * @category	Community Framework
 */
class LabelList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\label\Label';
}
