<?php
namespace wcf\data\page;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Executes page related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page
 * @category	Community Framework
 */
class PageAction extends AbstractDatabaseObjectAction implements IToggleAction {
	/**
	 * @inheritDoc
	 */
	protected $className = PageEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = array('admin.content.cms.canManagePage');
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = array('admin.content.cms.canManagePage');
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = array('admin.content.cms.canManagePage');
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = array('create', 'delete', 'toggle', 'update');
	
	/**
	 * @inheritDoc
	 */
	public function create() {
		$page = parent::create();
		
		// save page content
		if (!empty($this->parameters['content'])) {
			$sql = "INSERT INTO	wcf".WCF_N."_page_content
						(pageID, languageID, title, content, metaDescription, metaKeywords, customURL)
				VALUES		(?, ?, ?, ?, ?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($this->parameters['content'] as $languageID => $content) {
				$statement->execute(array(
					$page->pageID,
					($languageID ?: null),
					$content['title'],
					$content['content'],
					$content['metaDescription'],
					$content['metaKeywords'],
					$content['customURL']
				));
			}
		}
		
		return $page;
	}
	
	/**
	 * @inheritDoc
	 */
	public function update() {
		parent::update();
	
		// update page content
		if (!empty($this->parameters['content'])) {
			$sql = "DELETE FROM	wcf".WCF_N."_page_content
				WHERE		pageID = ?";
			$deleteStatement = WCF::getDB()->prepareStatement($sql);
			
			$sql = "INSERT INTO	wcf".WCF_N."_page_content
						(pageID, languageID, title, content, metaDescription, metaKeywords, customURL)
				VALUES		(?, ?, ?, ?, ?, ?, ?)";
			$insertStatement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($this->objects as $page) {
				$deleteStatement->execute(array($page->pageID));
				
				foreach ($this->parameters['content'] as $languageID => $content) {
					$insertStatement->execute(array(
						$page->pageID,
						($languageID ?: null),
						$content['title'],
						$content['content'],
						$content['metaDescription'],
						$content['metaKeywords'],
						$content['customURL']
					));
				}
			}
		}
		
		return $page;
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
	
	/**
	 * @inheritDoc
	 */
	public function validateToggle() {
		parent::validateUpdate();
		
		foreach ($this->objects as $object) {
			if (!$object->canDisable()) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function toggle() {
		foreach ($this->objects as $object) {
			$object->update(array('isDisabled' => ($object->isDisabled) ? 0 : 1));
		}
	}
}
