<?php
namespace wcf\data\style\variable;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of style variables.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Style\Variable
 *
 * @method	StyleVariable		current()
 * @method	StyleVariable[]		getObjects()
 * @method	StyleVariable|null	search($objectID)
 * @property	StyleVariable[]		$objects
 */
class StyleVariableList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = StyleVariable::class;
}
