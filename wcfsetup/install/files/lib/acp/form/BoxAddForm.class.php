<?php
namespace wcf\acp\form;
use wcf\data\box\Box;
use wcf\data\box\BoxAction;
use wcf\data\box\BoxEditor;
use wcf\data\media\Media;
use wcf\data\media\ViewableMediaList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\page\Page;
use wcf\data\page\PageNodeTree;
use wcf\form\AbstractForm;
use wcf\system\acl\simple\SimpleAclHandler;
use wcf\system\box\IBoxController;
use wcf\system\box\IConditionBoxController;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\language\LanguageFactory;
use wcf\system\page\handler\ILookupPageHandler;
use wcf\system\page\handler\IMenuPageHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the box add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	3.0
 */
class BoxAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cms.box.add';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.cms.canManageBox'];
	
	/**
	 * true if created box is multi-lingual
	 * @var	boolean
	 */
	public $isMultilingual = 0;
	
	/**
	 * box type
	 * @var	string
	 */
	public $boxType = '';
	
	/**
	 * box position
	 * @var	string
	 */
	public $position = 'contentTop';
	
	/**
	 * show order
	 * @var	integer
	 */
	public $showOrder = 0;
	
	/**
	 * true if created box is visible everywhere 
	 * @var	boolean
	 */
	public $visibleEverywhere = 1;
	
	/**
	 * css class name of created box
	 * @var	string
	 */
	public $cssClassName = '';
	
	/**
	 * true if box header is visible
	 * @var	boolean
	 */
	public $showHeader = 1;
	
	/**
	 * box name
	 * @var	string
	 */
	public $name = '';
	
	/**
	 * page titles
	 * @var	string[]
	 */
	public $title = [];
	
	/**
	 * page contents
	 * @var	string[]
	 */
	public $content = [];
	
	/**
	 * image ids
	 * @var	integer[]
	 */
	public $imageID = [];
	
	/**
	 * images
	 * @var	Media[]
	 */
	public $images = [];
	
	/**
	 * page ids
	 * @var	integer[]
	 */
	public $pageIDs = [];
	
	/**
	 * object type id of the selected box controller
	 * @var	integer
	 */
	public $boxControllerID = 0;
	
	/**
	 * selected box controller object type 
	 * @var	ObjectType
	 */
	public $boxController;
	
	/**
	 * link type
	 * @var string
	 */
	public $linkType = 'none';
	
	/**
	 * link page id
	 * @var int
	 */
	public $linkPageID = 0;
	
	/**
	 * link page object id
	 * @var int
	 */
	public $linkPageObjectID = 0;
	
	/**
	 * link external URL
	 * @var string
	 */
	public $externalURL = '';
	
	/**
	 * list of page handlers by page id
	 * @var	IMenuPageHandler[]
	 */
	public $pageHandlers = [];
	
	/**
	 * nested list of page nodes
	 * @var	\RecursiveIteratorIterator
	 */
	public $pageNodeList;
	
	/**
	 * acl values
	 * @var array
	 */
	public $aclValues = [];
	
	/**
	 * @var HtmlInputProcessor[]
	 */
	public $htmlInputProcessors = [];
	
	/**
	 * @var ObjectType[]
	 */
	public $availableBoxControllers = [];
	
	/**
	 * list of available positions per box handler
	 * @var array
	 */
	public $availableBoxPositions = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->readBoxType();
		
		$this->pageNodeList = (new PageNodeTree())->getNodeList();
		
		// fetch page handlers
		foreach ($this->pageNodeList as $pageNode) {
			$handler = $pageNode->getHandler();
			if ($handler !== null) {
				if ($handler instanceof ILookupPageHandler) {
					$this->pageHandlers[$pageNode->pageID] = $pageNode->requireObjectID;
				}
			}
		}
		
		$this->availableBoxControllers = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.boxController');
		$this->readBoxPositions();
	}
	
	/**
	 * Loads available box positions per box controller.
	 */
	protected function readBoxPositions() {
		foreach ($this->availableBoxControllers as $boxController) {
			/** @var IBoxController $controller */
			$controller = $boxController->getProcessor();
			$this->availableBoxPositions[$boxController->objectTypeID] = $controller::getSupportedPositions();
		}
	}
	
	/**
	 * Reads basic page parameters controlling type and i18n.
	 *
	 * @throws	IllegalLinkException
	 */
	protected function readBoxType() {
		if (!empty($_REQUEST['isMultilingual'])) $this->isMultilingual = 1;
		if (!empty($_REQUEST['boxType'])) $this->boxType = $_REQUEST['boxType'];
		
		// work-around to force adding boxes via dialog overlay
		if (empty($_POST) && $this->boxType == '') {
			HeaderUtil::redirect(LinkHandler::getInstance()->getLink('BoxList', ['showBoxAddDialog' => 1]));
			exit;
		}
		
		// validate box type
		if (!in_array($this->boxType, Box::$availableBoxTypes)) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->visibleEverywhere = $this->showHeader = $this->showOrder = 0;
		if (isset($_POST['name'])) $this->name = StringUtil::trim($_POST['name']);
		if (isset($_POST['boxType'])) $this->boxType = $_POST['boxType'];
		if (isset($_POST['position'])) $this->position = $_POST['position'];
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
		if (isset($_POST['visibleEverywhere'])) $this->visibleEverywhere = intval($_POST['visibleEverywhere']);
		if (isset($_POST['cssClassName'])) $this->cssClassName = StringUtil::trim($_POST['cssClassName']);
		if (isset($_POST['showHeader'])) $this->showHeader = intval($_POST['showHeader']);
		if (isset($_POST['pageIDs']) && is_array($_POST['pageIDs'])) $this->pageIDs = ArrayUtil::toIntegerArray($_POST['pageIDs']);
		
		if (isset($_POST['linkType'])) $this->linkType = $_POST['linkType'];
		if (!empty($_POST['linkPageID'])) $this->linkPageID = intval($_POST['linkPageID']);
		if (!empty($_POST['linkPageObjectID'])) $this->linkPageObjectID = intval($_POST['linkPageObjectID']);
		if (isset($_POST['externalURL'])) $this->externalURL = StringUtil::trim($_POST['externalURL']);
		
		if (isset($_POST['title']) && is_array($_POST['title'])) $this->title = ArrayUtil::trim($_POST['title']);
		if (isset($_POST['content']) && is_array($_POST['content'])) $this->content = ArrayUtil::trim($_POST['content']);
		if (isset($_POST['boxControllerID'])) $this->boxControllerID = intval($_POST['boxControllerID']);
		if (isset($_POST['aclValues']) && is_array($_POST['aclValues'])) $this->aclValues = $_POST['aclValues'];
		
		if (WCF::getSession()->getPermission('admin.content.cms.canUseMedia')) {
			if (isset($_POST['imageID']) && is_array($_POST['imageID'])) $this->imageID = ArrayUtil::toIntegerArray($_POST['imageID']);
			
			$this->readBoxImages();
		}
		
		if ($this->boxType === 'system') {
			$this->boxController = ObjectTypeCache::getInstance()->getObjectType($this->boxControllerID);
		}
	}
	
	/**
	 * Reads the box images.
	 */
	protected function readBoxImages() {
		if (!empty($this->imageID)) {
			$mediaList = new ViewableMediaList();
			$mediaList->setObjectIDs($this->imageID);
			$mediaList->readObjects();
			
			foreach ($this->imageID as $languageID => $imageID) {
				$image = $mediaList->search($imageID);
				if ($image !== null && $image->isImage) {
					$this->images[$languageID] = $image;
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		// validate name
		$this->validateName();
		
		// validate controller
		if ($this->boxType === 'system') {
			if ($this->boxController === null || $this->boxController->getDefinition()->definitionName != 'com.woltlab.wcf.boxController') {
				throw new UserInputException('boxController');
			}
			
			if ($this->boxController && $this->boxController->getProcessor() instanceof IConditionBoxController) {
				$this->boxController->getProcessor()->readConditions();
			}
		}
		else {
			$this->boxControllerID = 0;
		}
		
		// validate box position
		$this->validateBoxPosition();
		
		// validate link
		if ($this->linkType == 'internal') {
			$this->externalURL = '';
			
			if (!$this->linkPageID) {
				throw new UserInputException('linkPageID');
			}
			$page = new Page($this->linkPageID);
			if (!$page->pageID) {
				throw new UserInputException('linkPageID', 'invalid');
			}
			
			// validate page object id
			if (isset($this->pageHandlers[$page->pageID])) {
				if ($this->pageHandlers[$page->pageID] && !$this->linkPageObjectID) {
					throw new UserInputException('linkPageObjectID');
				}
				
				/** @var ILookupPageHandler $handler */
				$handler = $page->getHandler();
				if ($this->linkPageObjectID && !$handler->isValid($this->linkPageObjectID)) {
					throw new UserInputException('linkPageObjectID', 'invalid');
				}
			}
		}
		else if ($this->linkType == 'external') {
			$this->linkPageID = $this->linkPageObjectID = null;
			
			if (empty($this->externalURL)) {
				throw new UserInputException('externalURL');
			}
		}
		else {
			$this->linkPageID = $this->linkPageObjectID = null;
			$this->externalURL = '';
		}
		
		if ($this->boxController && $this->boxController->getProcessor() instanceof IConditionBoxController) {
			$this->boxController->getProcessor()->validateConditions();
		}
		
		// validate images
		if (WCF::getSession()->getPermission('admin.content.cms.canUseMedia')) {
			foreach ($this->imageID as $languageID => $imageID) {
				if (!isset($this->imageID[$languageID])) {
					throw new UserInputException('imageID' . $languageID);
				}
			}
		}
		
		// box title
		if ($this->showHeader) {
			if ($this->boxType == 'system' || $this->isMultilingual) {
				foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
					if (empty($this->title[$language->languageID])) throw new UserInputException('title'.$language->languageID);
				}
			}
			else {
				if (empty($this->title[0])) throw new UserInputException('title');
			}
		}
		
		if ($this->boxType == 'text') {
			if ($this->isMultilingual) {
				foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
					$this->htmlInputProcessors[$language->languageID] = new HtmlInputProcessor();
					$this->htmlInputProcessors[$language->languageID]->process((!empty($this->content[$language->languageID]) ? $this->content[$language->languageID] : ''), 'com.woltlab.wcf.box.content');
				}
			}
			else {
				$this->htmlInputProcessors[0] = new HtmlInputProcessor();
				$this->htmlInputProcessors[0]->process((!empty($this->content[0]) ? $this->content[0] : ''), 'com.woltlab.wcf.box.content');
			}
		}
		
		// validate page ids
		if (!empty($this->pageIDs)) {
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('pageID IN (?)', [$this->pageIDs]);
			$sql = "SELECT	pageID
				FROM	wcf".WCF_N."_page
				" . $conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditionBuilder->getParameters());
			$this->pageIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		}
	}
	
	/**
	 * Validates box name.
	 */
	protected function validateName() {
		if (empty($this->name)) {
			throw new UserInputException('name');
		}
		if (Box::getBoxByName($this->name)) {
			throw new UserInputException('name', 'notUnique');
		}
	}
	
	/**
	 * Validates the selected box position.
	 */
	protected function validateBoxPosition() {
		if (!in_array($this->position, Box::$availablePositions)) {
			throw new UserInputException('position');
		}
		
		if ($this->boxType == 'system') {
			if (!in_array($this->position, $this->availableBoxPositions[$this->boxController->objectTypeID])) {
				throw new UserInputException('position', 'invalid');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$content = [];
		if ($this->boxType == 'system' || $this->isMultilingual) {
			foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
				$content[$language->languageID] = [
					'title' => !empty($this->title[$language->languageID]) ? $this->title[$language->languageID] : '',
					'content' => !empty($this->content[$language->languageID]) ? $this->content[$language->languageID] : '',
					'htmlInputProcessor' => isset($this->htmlInputProcessors[$language->languageID]) ? $this->htmlInputProcessors[$language->languageID] : null,
					'imageID' => !empty($this->imageID[$language->languageID]) ? $this->imageID[$language->languageID] : null
				];
			}
		}
		else {
			$content[0] = [
				'title' => !empty($this->title[0]) ? $this->title[0] : '',
				'content' => !empty($this->content[0]) ? $this->content[0] : '',
				'htmlInputProcessor' => isset($this->htmlInputProcessors[0]) ? $this->htmlInputProcessors[0] : null,
				'imageID' => !empty($this->imageID[0]) ? $this->imageID[0] : null
			];
		}
		
		$data = [
			'name' => $this->name,
			'packageID' => 1,
			'isMultilingual' => $this->isMultilingual,
			'boxType' => $this->boxType,
			'position' => $this->position,
			'showOrder' => $this->showOrder,
			'visibleEverywhere' => $this->visibleEverywhere,
			'cssClassName' => $this->cssClassName,
			'showHeader' => $this->showHeader,
			'linkPageID' => $this->linkPageID,
			'linkPageObjectID' => $this->linkPageObjectID ?: 0,
			'externalURL' => $this->externalURL,
			'identifier' => ''
		];
		if ($this->boxControllerID) {
			$data['objectTypeID'] = $this->boxControllerID;
		}
		
		$this->objectAction = new BoxAction([], 'create', ['data' => array_merge($this->additionalFields, $data), 'content' => $content, 'pageIDs' => $this->pageIDs]);
		$box = $this->objectAction->executeAction()['returnValues'];
		
		// set generic box identifier
		$boxEditor = new BoxEditor($box);
		$boxEditor->update([
			'identifier' => 'com.woltlab.wcf.genericBox'.$boxEditor->boxID
		]);
		
		if ($this->boxController) {
			if ($this->boxController->getProcessor() instanceof IConditionBoxController) {
				$this->boxController->getProcessor()->setBox($box, false);
			}
			else {
				$this->boxController->getProcessor()->setBox($box);
			}
			
			$this->boxController->getProcessor()->saveAdditionalData();
		}
		
		// save acl
		SimpleAclHandler::getInstance()->setValues('com.woltlab.wcf.box', $box->boxID, $this->aclValues);
		
		// call saved event
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
		
		// reset variables
		$this->cssClassName = $this->name = '';
		$this->position = 'contentTop';
		$this->showOrder = $this->boxControllerID = 0;
		$this->visibleEverywhere = $this->showHeader = 1;
		$this->title = $this->content = $this->images = $this->imageID = $this->pageIDs = $this->aclValues = [];
		$this->boxController = null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'action' => 'add',
			'isMultilingual' => $this->isMultilingual,
			'name' => $this->name,
			'boxType' => $this->boxType,
			'position' => $this->position,
			'cssClassName' => $this->cssClassName,
			'showOrder' => $this->showOrder,
			'visibleEverywhere' => $this->visibleEverywhere,
			'showHeader' => $this->showHeader,
			'title' => $this->title,
			'content' => $this->content,
			'imageID' => $this->imageID,
			'images' => $this->images,
			'pageIDs' => $this->pageIDs,
			'linkType' => $this->linkType,
			'linkPageID' => $this->linkPageID,
			'linkPageObjectID' => $this->linkPageObjectID,
			'externalURL' => $this->externalURL,
			'availableLanguages' => LanguageFactory::getInstance()->getLanguages(),
			'availableBoxTypes' => Box::$availableBoxTypes,
			'availablePositions' => Box::$availablePositions,
			'availableBoxControllers' => $this->availableBoxControllers,
			'boxController' => $this->boxController,
			'pageNodeList' => $this->pageNodeList,
			'pageHandlers' => $this->pageHandlers,
			'aclValues' => SimpleAclHandler::getInstance()->getOutputValues($this->aclValues),
			'availableBoxPositions' => $this->availableBoxPositions
		]);
	}
}
