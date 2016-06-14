<?php
namespace wcf\data\label\group;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes label group-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Label\Group
 * 
 * @method	LabelGroup		create()
 * @method	LabelGroupEditor[]	getObjects()
 * @method	LabelGroupEditor	getSingleObject()
 */
class LabelGroupAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = LabelGroupEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.content.label.canManageLabel'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.content.label.canManageLabel'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.content.label.canManageLabel'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'update'];
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		$count = parent::delete();
		
		foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.label.objectType') as $objectType) {
			$objectType->getProcessor()->save();
		}
		
		return $count;
	}
}
