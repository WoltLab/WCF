<?php
namespace wcf\data\option;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Option
 *
 * @method	Option		current()
 * @method	Option[]	getObjects()
 * @method	Option|null	search($objectID)
 * @property	Option[]	$objects
 */
class OptionList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Option::class;
}
