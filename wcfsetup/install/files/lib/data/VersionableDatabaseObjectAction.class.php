<?php
namespace wcf\data;
use wcf\system\version\VersionHandler;

/**
 * Abstract class for all versionable data actions.
 * 
 * @deprecated	2.1 - will be removed with WCF 2.2
 * 
 * @author	Jeffrey Reichardt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 */
abstract class VersionableDatabaseObjectAction extends AbstractDatabaseObjectAction {
	/**
	 * Validates restoring a version.
	 */
	public function validateRestoreRevision() {
		parent::validateUpdate();
	}
	
	/**
	 * @see	\wcf\data\IDeleteAction::delete()
	 */
	public function delete() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		// get ids
		$objectIDs = array();
		foreach ($this->objects as $object) {
			$objectIDs[] = $object->getObjectID();
		}
		
		// execute action
		return call_user_func(array($this->className, 'deleteAll'), $objectIDs);
	}
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::update()
	 */
	public function update() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		if (isset($this->parameters['data'])) {
			foreach ($this->objects as $object) {
				$object->update($this->parameters['data']);
			}
			
			// create revision retroactively
			$this->createRevision();
		}
	}
	
	/**
	 * Creates a new revision.
	 */
	protected function createRevision() {
		$indexName = call_user_func(array($this->className, 'getDatabaseTableIndexName'));
		
		foreach ($this->objects as $object) {
			call_user_func(array($this->className, 'createRevision'), array_merge($object->getDecoratedObject()->getData(), array($indexName => $object->getObjectID())));
		}
	}
	
	/**
	 * Deletes a revision.
	 */
	protected function deleteRevision() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		// get ids
		$objectIDs = array();
		foreach ($this->objects as $object) {
			$objectIDs[] = $object->getObjectID();
		}
		
		// execute action
		return call_user_func(array($this->className, 'deleteRevision'), $objectIDs);
	}
	
	/**
	 * Restores a revision.
	 */
	public function restoreRevision() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		// currently we only support restoring one version
		foreach ($this->objects as $object) {
			$objectType = VersionHandler::getInstance()->getObjectTypeByName($object->versionableObjectTypeName);
			$restoreObject = VersionHandler::getInstance()->getVersionByID($objectType->objectTypeID, $this->parameters['restoreObjectID']);
			
			$this->parameters['data'] = $restoreObject->getData();
		}
		
		$this->update();
	}
}
