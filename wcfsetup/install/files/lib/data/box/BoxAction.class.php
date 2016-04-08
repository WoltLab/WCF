<?php
namespace wcf\data\box;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Executes box related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.box
 * @category	Community Framework
 * @since	2.2
 */
class BoxAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = BoxEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.content.cms.canManageBox'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.content.cms.canManageBox'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.content.cms.canManageBox'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'update'];
	
	/**
	 * @inheritDoc
	 */
	public function create() {
		$box = parent::create();
	
		// save box content
		if (!empty($this->parameters['content'])) {
			$sql = "INSERT INTO	wcf".WCF_N."_box_content
						(boxID, languageID, title, content, imageID)
				VALUES		(?, ?, ?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($this->parameters['content'] as $languageID => $content) {
				$statement->execute([
					$box->boxID,
					($languageID ?: null),
					$content['title'],
					$content['content'],
					$content['imageID']
				]);
			}
		}
		
		// save box to page
		if (!empty($this->parameters['pageIDs'])) {
			$sql = "INSERT INTO	wcf".WCF_N."_box_to_page
						(boxID, pageID, visible)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($this->parameters['pageIDs'] as $pageID) {
				$statement->execute([
					$box->boxID,
					$pageID,
					($box->visibleEverywhere ? 0 : 1)
				]);
			}
		}
		
		return $box;
	}
	
	/**
	 * @inheritDoc
	 */
	public function update() {
		parent::update();
		
		// update box content
		if (!empty($this->parameters['content'])) {
			$sql = "DELETE FROM	wcf".WCF_N."_box_content
				WHERE		boxID = ?";
			$deleteStatement = WCF::getDB()->prepareStatement($sql);
			
			$sql = "INSERT INTO	wcf".WCF_N."_box_content
						(boxID, languageID, title, content, imageID)
				VALUES		(?, ?, ?, ?, ?)";
			$insertStatement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($this->objects as $box) {
				$deleteStatement->execute([$box->boxID]);
				
				foreach ($this->parameters['content'] as $languageID => $content) {
					$insertStatement->execute([
						$box->boxID,
						($languageID ?: null),
						$content['title'],
						$content['content'],
						$content['imageID']
					]);
				}
			}
		}
		
		// save box to page
		if (isset($this->parameters['pageIDs'])) {
			$sql = "DELETE FROM	wcf".WCF_N."_box_to_page
				WHERE		boxID = ?";
			$deleteStatement = WCF::getDB()->prepareStatement($sql);
			
			$sql = "INSERT INTO	wcf".WCF_N."_box_to_page
						(boxID, pageID, visible)
				VALUES		(?, ?, ?)";
			$insertStatement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($this->objects as $box) {
				$deleteStatement->execute([$box->boxID]);
				
				foreach ($this->parameters['pageIDs'] as $pageID) {
					$insertStatement->execute([$box->boxID, $pageID, ($box->visibleEverywhere ? 0 : 1)]);
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateDelete() {
		parent::validateDelete();
		
		foreach ($this->objects as $object) {
			if (!$object->canDelete()) {
				throw new PermissionDeniedException();
			}
		}
	}
}
