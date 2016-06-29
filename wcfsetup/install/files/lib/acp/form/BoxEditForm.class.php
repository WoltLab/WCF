<?php
namespace wcf\acp\form;
use wcf\data\box\Box;
use wcf\data\box\BoxAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\form\AbstractForm;
use wcf\system\acl\simple\SimpleAclHandler;
use wcf\system\box\IConditionBoxController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Shows the box edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	3.0
 */
class BoxEditForm extends BoxAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cms.box.list';
	
	/**
	 * box id
	 * @var	integer
	 */
	public $boxID = 0;
	
	/**
	 * box object
	 * @var	Box
	 */
	public $box = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
	
		if (isset($_REQUEST['id'])) $this->boxID = intval($_REQUEST['id']);
		$this->box = new Box($this->boxID);
		if (!$this->box->boxID) {
			throw new IllegalLinkException();
		}
		if ($this->box->boxType == 'menu') {
			// it's not allowed to edit menu boxes directly
			throw new IllegalLinkException();
		}
		if ($this->box->isMultilingual) $this->isMultilingual = 1;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function readBoxType() {
		// not required for editing
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateName() {
		if (mb_strtolower($this->name) != mb_strtolower($this->box->name)) {
			parent::validateName();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		$content = [];
		if ($this->boxType == 'system' || $this->isMultilingual) {
			foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
				$content[$language->languageID] = [
					'title' => (!empty($this->title[$language->languageID]) ? $this->title[$language->languageID] : ''),
					'content' => (!empty($this->content[$language->languageID]) ? $this->content[$language->languageID] : ''),
					'htmlInputProcessor' => (isset($this->htmlInputProcessors[$language->languageID]) ? $this->htmlInputProcessors[$language->languageID] : null),
					'imageID' => (!empty($this->imageID[$language->languageID]) ? $this->imageID[$language->languageID] : null)
				];
			}
		}
		else {
			$content[0] = [
				'title' => (!empty($this->title[0]) ? $this->title[0] : ''),
				'content' => (!empty($this->content[0]) ? $this->content[0] : ''),
				'htmlInputProcessor' => (isset($this->htmlInputProcessors[0]) ? $this->htmlInputProcessors[0] : null),
				'imageID' => (!empty($this->imageID[0]) ? $this->imageID[0] : null)
			];
		}
		
		$data = [
			'name' => $this->name,
			'isMultilingual' => $this->isMultilingual,
			'boxType' => $this->boxType,
			'position' => $this->position,
			'showOrder' => $this->showOrder,
			'visibleEverywhere' => $this->visibleEverywhere,
			'cssClassName' => $this->cssClassName,
			'showHeader' => $this->showHeader,
			'linkPageID' => $this->linkPageID,
			'linkPageObjectID' => ($this->linkPageObjectID ?: 0),
			'externalURL' => $this->externalURL
		];
		if ($this->boxControllerID) {
			$data['objectTypeID'] = $this->boxControllerID;
		}
		
		$this->objectAction = new BoxAction([$this->box], 'update', ['data' => array_merge($this->additionalFields, $data), 'content' => $content, 'pageIDs' => $this->pageIDs]);
		$this->objectAction->executeAction();
		
		if ($this->boxController && $this->boxController->getProcessor() instanceof IConditionBoxController) {
			$this->boxController->getProcessor()->setBox($this->box, false);
			$this->boxController->getProcessor()->saveConditions();
		}
		
		SimpleAclHandler::getInstance()->setValues('com.woltlab.wcf.box', $this->box->boxID, $this->aclValues);
		
		// call saved event
		$this->saved();
		
		// show success
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		if (!empty($_POST) && !WCF::getSession()->getPermission('admin.content.cms.canUseMedia')) {
			foreach ($this->box->getBoxContents() as $languageID => $content) {
				$this->imageID[$languageID] = $content->imageID;
			}
			
			$this->readBoxImages();
		}
		
		parent::readData();
		
		if (empty($_POST)) {
			$this->name = $this->box->name;
			$this->boxType = $this->box->boxType;
			$this->position = $this->box->position;
			$this->showOrder = $this->box->showOrder;
			$this->cssClassName = $this->box->cssClassName;
			$this->boxControllerID = $this->box->objectTypeID;
			if ($this->box->showHeader) $this->showHeader = 1;
			else $this->showHeader = 0;
			if ($this->box->visibleEverywhere) $this->visibleEverywhere = 1;
			else $this->visibleEverywhere = 0;
			$this->pageIDs = $this->box->getPageIDs();
			$this->linkPageID = $this->box->linkPageID;
			$this->linkPageObjectID = $this->box->linkPageObjectID;
			$this->externalURL = $this->box->externalURL;
			if ($this->linkPageID) $this->linkType = 'internal';
			if ($this->externalURL) $this->linkType = 'external';
			
			foreach ($this->box->getBoxContents() as $languageID => $content) {
				$this->title[$languageID] = $content->title;
				$this->content[$languageID] = $content->content;
				$this->imageID[$languageID] = $content->imageID;
			}
			
			if ($this->boxControllerID) {
				$this->boxController = ObjectTypeCache::getInstance()->getObjectType($this->boxControllerID);
				if ($this->boxController->getProcessor() instanceof IConditionBoxController) {
					$this->boxController->getProcessor()->setBox($this->box);
				}
			}
			
			$this->aclValues = SimpleAclHandler::getInstance()->getValues('com.woltlab.wcf.box', $this->box->boxID);
			
			$this->readBoxImages();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'action' => 'edit',
			'boxID' => $this->boxID,
			'box' => $this->box
		]);
	}
}
