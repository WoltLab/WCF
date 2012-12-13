<?php
namespace wcf\data;

/**
 * Abstract class for all versionable data actions.
 *
 * @author		Jeffrey Reichardt
 * @copyright	2001-2012 WoltLab GmbH
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category 	Community Framework
 */
abstract class VersionableDatabaseObjectAction extends AbstractDatabaseObjectAction {
	
	/**
	 * Validates restoring an version
	 */
	public function validateRestore() {
		parent::validateUpdate();
	}
	
	/**
	 * Deletes database object and returns the number of deleted objects.
	 *
	 * @return	integer
	 */
	public function delete() {
		if (!count($this->objects)) {
			$this->readObjects();
		}
		
		// get index name
		$indexName = call_user_func(array($this->className, 'getDatabaseTableIndexName'));
		
		// get ids
		$objectIDs = array();
		foreach ($this->objects as $object) {
			$objectIDs[] = $object->__get($indexName);
		}
		
		// execute action
		return call_user_func(array($this->className, 'deleteAll'), $objectIDs);
	}
	
	/**
	 * Updates data.
	 */
	public function update() {
		if (!count($this->objects)) {
			$this->readObjects();
		}
		
		//get index name
		$indexName = call_user_func(array($this->className, 'getDatabaseTableIndexName'));
		
		if (isset($this->parameters['data'])) {
			foreach ($this->objects as $object) {
				$this->update($this->parameters['data']);
				//create revision retroactively
				$this->createRevision();
			}
		}
	}
	
	/**
	 * Creates a new revision.
	 */
	protected function createRevision() {
		$indexName = call_user_func(array($this->className, 'getDatabaseTableIndexName'));
	
		foreach($this->objects as $object) {
			call_user_func(array($this->className, 'createRevision'), array_merge($object->getData(), array($indexName =>$object->__get($indexName))));
		}
	}
	
	/**
	 * Deletes revision.
	 */
	protected function deleteRevision() {
		if (!count($this->objects)) {
			$this->readObjects();
		}
		
		// get index name
		$indexName = call_user_func(array($this->className, 'getDatabaseTableIndexName'));
		
		// get ids
		$objectIDs = array();
		foreach ($this->objects as $object) {
			$objectIDs[] = $object->__get($indexName);
		}
		
		// execute action
		return call_user_func(array($this->className, 'deleteRevision'), $objectIDs);
	}
	
	/**
	 * Restores an revision.
	 */
	public function restore() {
		if (!count($this->objects)) {
			$this->readObjects();
		}

		//currently we only support restoring one version
		foreach($this->objects as $object) {
			$objectType = VersionHandler::getInstance()->getObjectTypeByName($object->objectTypeName);
			$restoreObject = VersionHandler::getInstance()->getVersionByID($objectType->objectTypeID, $this->parameters['restoreObjectID']);

			$this->parameters['data'] = $restoreObject->getData();
		}
		
		$this->update();
	}
}
