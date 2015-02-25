<?php
namespace wcf\data\clipboard\action;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of clipboard actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.clipboard.action
 * @category	Community Framework
 */
class ClipboardActionList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\clipboard\action\ClipboardAction';
}
