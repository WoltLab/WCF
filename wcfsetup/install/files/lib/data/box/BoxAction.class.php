<?php
namespace wcf\data\box;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\box\IConditionBoxController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes box related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
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
	protected $requireACP = ['create', 'delete', 'getBoxConditionsTemplate', 'update'];
	
	/**
	 * object type for which the conditions template is fetched
	 * @var	ObjectType
	 */
	public $boxController;
	
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
		
		// save template
		if ($box->boxType == 'tpl') {
			if (!empty($this->parameters['content'])) {
				foreach ($this->parameters['content'] as $languageID => $content) {
					file_put_contents(WCF_DIR . 'templates/' . $box->getTplName(($languageID ?: null)) . '.tpl', $content['content']);
				}
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
				
				// save template
				if ($box->boxType == 'tpl') {
					foreach ($this->parameters['content'] as $languageID => $content) {
						file_put_contents(WCF_DIR . 'templates/' . $box->getTplName(($languageID ?: null)) . '.tpl', $content['content']);
					}
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
				$visibleEverywhere = (isset($this->parameters['data']['visibleEverywhere']) ? $this->parameters['data']['visibleEverywhere'] : $box->visibleEverywhere);
				
				foreach ($this->parameters['pageIDs'] as $pageID) {
					$insertStatement->execute([$box->boxID, $pageID, ($visibleEverywhere ? 0 : 1)]);
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
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		foreach ($this->objects as $box) {
			if ($box->boxType == 'tpl') {
				foreach ($box->getBoxContent() as $languageID => $content) {
					$file = WCF_DIR . 'templates/' . $box->getTplName(($languageID ?: null)) . '.tpl';
					if (file_exists($file)) {
						@unlink($file);
					}
				}
			}
		}
		
		parent::delete();
	}
	
	/**
	 * Validates the 'getBoxConditionsTemplate' action.
	 */
	public function validateGetBoxConditionsTemplate() {
		WCF::getSession()->checkPermissions(['admin.content.cms.canManageBox']);
		
		$this->readInteger('objectTypeID');
		$this->boxController = ObjectTypeCache::getInstance()->getObjectType($this->parameters['objectTypeID']);
		if ($this->boxController === null) {
			throw new UserInputException('objectTypeID');
		}
	}
	
	/**
	 * Returns the template
	 * 
	 * @return	mixed[]
	 */
	public function getBoxConditionsTemplate() {
		return [
			'objectTypeID' => $this->boxController->objectTypeID,
			'template' => $this->boxController->getProcessor() instanceof IConditionBoxController ? $this->boxController->getProcessor()->getConditionsTemplate() : ''
		];
	}
}
