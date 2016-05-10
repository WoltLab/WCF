<?php
namespace wcf\data\modification\log;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of modification logs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.modification.log
 * @category	Community Framework
 *
 * @method	ModificationLog		current()
 * @method	ModificationLog[]	getObjects()
 * @method	ModificationLog|null	search($objectID)
 * @property	ModificationLog[]	$objects
 */
class ModificationLogList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = ModificationLog::class;
}
