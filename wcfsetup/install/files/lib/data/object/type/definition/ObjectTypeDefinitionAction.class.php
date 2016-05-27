<?php
namespace wcf\data\object\type\definition;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes object type definition-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.object.type.definition
 * @category	Community Framework
 * 
 * @method	ObjectTypeDefinition		create()
 * @method	ObjectTypeDefinitionEditor[]	getObjects()
 * @method	ObjectTypeDefinitionEditor	getSingleObject()
 */
class ObjectTypeDefinitionAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = ObjectTypeDefinitionEditor::class;
}
