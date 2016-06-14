<?php
namespace wcf\data\object\type\definition;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit object type definitions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Object\Type\Definition
 * 
 * @method	ObjectTypeDefinition	getDecoratedObject()
 * @mixin	ObjectTypeDefinition
 */
class ObjectTypeDefinitionEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ObjectTypeDefinition::class;
}
