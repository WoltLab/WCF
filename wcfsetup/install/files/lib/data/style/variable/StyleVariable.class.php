<?php
namespace wcf\data\style\variable;
use wcf\data\DatabaseObject;

/**
 * Represents a style variable.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Style\Variable
 *
 * @property-read	integer		$variableID		unique id of the style variable
 * @property-read	string		$variableName		name of the style variable
 * @property-read	string		$defaultValue		default value of the style variable
 */
class StyleVariable extends DatabaseObject {
	const TYPE_COLOR = 'color';
	const TYPE_TEXT = 'text';
	const TYPE_UNIT = 'unit';
}
