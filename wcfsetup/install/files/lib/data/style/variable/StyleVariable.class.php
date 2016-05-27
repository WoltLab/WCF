<?php
namespace wcf\data\style\variable;
use wcf\data\DatabaseObject;

/**
 * Represents a style variable.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.style.variable
 * @category	Community Framework
 *
 * @property-read	integer		$variableID
 * @property-read	string		$variableName
 * @property-read	string		$defaultValue
 */
class StyleVariable extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'style_variable';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'variableID';
	
	const TYPE_COLOR = 'color';
	const TYPE_TEXT = 'text';
	const TYPE_UNIT = 'unit';
}
