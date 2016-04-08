<?php
namespace wcf\acp\form;
use wcf\data\box\Box;
use wcf\data\box\BoxAction;
use wcf\data\box\BoxEditor;
use wcf\data\media\Media;
use wcf\data\media\ViewableMediaList;
use wcf\data\page\PageNodeTree;
use wcf\form\AbstractForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Shows the box add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 * @since	2.2
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
	public $position = '';
	
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
	 * php class name
	 * @var	string
	 */
	public $className = '';
	
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
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
	
		if (!empty($_REQUEST['isMultilingual'])) $this->isMultilingual = 1;
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
		if (isset($_POST['className'])) $this->className = StringUtil::trim($_POST['className']);
		if (isset($_POST['pageIDs']) && is_array($_POST['pageIDs'])) $this->pageIDs = ArrayUtil::toIntegerArray($_POST['pageIDs']);
		
		if (isset($_POST['title']) && is_array($_POST['title'])) $this->title = ArrayUtil::trim($_POST['title']);
		if (isset($_POST['content']) && is_array($_POST['content'])) $this->content = ArrayUtil::trim($_POST['content']);
		
		if (WCF::getSession()->getPermission('admin.content.cms.canUseMedia')) {
			if (isset($_POST['imageID']) && is_array($_POST['imageID'])) $this->imageID = ArrayUtil::toIntegerArray($_POST['imageID']);
			
			$this->readBoxImages();
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
		
		// validate box type
		if (!in_array($this->boxType, Box::$availableBoxTypes)) {
			throw new UserInputException('boxType');
		}
		
		// validate box position
		if (!in_array($this->position, Box::$availablePositions)) {
			throw new UserInputException('position');
		}
		
		// validate class name
		if ($this->boxType == 'system') {
			if (empty($this->className)) {
				throw new UserInputException('className');
			}
			
			// @todo check class
		}
		
		// validate page ids
		if (!empty($this->pageIDs)) {
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('pageID IN (?)', [$this->pageIDs]);
			$sql = "SELECT  pageID
				FROM    wcf".WCF_N."_page
				" . $conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditionBuilder->getParameters());
			$this->pageIDs = [];
			while ($row = $statement->fetchArray()) {
				$this->pageIDs[] = $row['pageID'];
			}
		}
		
		// validate images
		if (WCF::getSession()->getPermission('admin.content.cms.canUseMedia')) {
			foreach ($this->imageID as $languageID => $imageID) {
				if (!isset($this->imageID[$languageID])) {
					throw new UserInputException('imageID' . $languageID);
				}
			}
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
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$content = [];
		if ($this->isMultilingual) {
			foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
				$content[$language->languageID] = [
					'title' => (!empty($this->title[$language->languageID]) ? $this->title[$language->languageID] : ''),
					'content' => (!empty($this->content[$language->languageID]) ? $this->content[$language->languageID] : ''),
					'imageID' => (!empty($this->imageID[$language->languageID]) ? $this->imageID[$language->languageID] : null)
				];
			}
		}
		else {
			$content[0] = [
				'title' => (!empty($this->title[0]) ? $this->title[0] : ''),
				'content' => (!empty($this->content[0]) ? $this->content[0] : ''),
				'imageID' => (!empty($this->imageID[0]) ? $this->imageID[0] : null)
			];
		}
		
		$this->objectAction = new BoxAction([], 'create', ['data' => array_merge($this->additionalFields, [
			'name' => $this->name,
			'packageID' => 1,
			'isMultilingual' => $this->isMultilingual,
			'boxType' => $this->boxType,
			'position' => $this->position,
			'showOrder' => $this->showOrder,
			'visibleEverywhere' => $this->visibleEverywhere,
			'cssClassName' => $this->cssClassName,
			'showHeader' => $this->showHeader,
			'className' => $this->className,
			'identifier' => ''
		]), 'content' => $content, 'pageIDs' => $this->pageIDs ]);
		$returnValues = $this->objectAction->executeAction();
		
		// set generic box identifier
		$boxEditor = new BoxEditor($returnValues['returnValues']);
		$boxEditor->update([
			'identifier' => 'com.woltlab.wcf.genericBox'.$boxEditor->boxID
		]);
		
		// call saved event
		$this->saved();
		
		// show success
		WCF::getTPL()->assign('success', true);
		
		// reset variables
		$this->boxType = $this->position = $this->cssClassName = $this->className = $this->name = '';
		$this->showOrder = 0;
		$this->visibleEverywhere = $this->showHeader = 1;
		$this->title = $this->content = $this->images = $this->imageID = [];
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
			'className' => $this->className,
			'showOrder' => $this->showOrder,
			'visibleEverywhere' => $this->visibleEverywhere,
			'showHeader' => $this->showHeader,
			'title' => $this->title,
			'content' => $this->content,
			'imageID' => $this->imageID,
			'images' => $this->images,
			'pageIDs' => $this->pageIDs,
			'availableLanguages' => LanguageFactory::getInstance()->getLanguages(),
			'availableBoxTypes' => Box::$availableBoxTypes,
			'availablePositions' => Box::$availablePositions,
			'pageNodeList' => (new PageNodeTree())->getNodeList()
		]);
	}
}
