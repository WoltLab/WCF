<?php
namespace wcf\data\object\type;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes object type-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.object.type
 * @category	Community Framework
 * 
 * @method	ObjectType		create()
 * @method	ObjectTypeEditor[]	getObjects()
 * @method	ObjectTypeEditor	getSingleObject()
 */
class ObjectTypeAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = ObjectTypeEditor::class;
}
