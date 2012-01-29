<?php
namespace wcf\data\object\type;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit object types.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.object.type
 * @category 	Community Framework
 */
class ObjectTypeEditor extends DatabaseObjectEditor {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\object\type\ObjectType';
}
