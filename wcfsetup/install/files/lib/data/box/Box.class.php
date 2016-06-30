<?php
namespace wcf\data\box;
use wcf\data\box\content\BoxContent;
use wcf\data\condition\Condition;
use wcf\data\media\ViewableMedia;
use wcf\data\menu\Menu;
use wcf\data\menu\MenuCache;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\acl\simple\SimpleAclResolver;
use wcf\system\box\IBoxController;
use wcf\system\box\IConditionBoxController;
use wcf\system\condition\ConditionHandler;
use wcf\data\page\Page;
use wcf\data\page\PageCache;
use wcf\data\DatabaseObject;
use wcf\system\exception\SystemException;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\page\handler\ILookupPageHandler;
use wcf\system\page\handler\IMenuPageHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a box.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Box
 * @since	3.0
 *
 * @property-read	integer		$boxID
 * @property-read	integer|null	$objectTypeID		id of the box controller object type
 * @property-read	string		$identifier
 * @property-read	string		$name
 * @property-read	string		$boxType
 * @property-read	string		$position
 * @property-read	integer		$showOrder
 * @property-read	integer		$visibleEverywhere
 * @property-read	integer		$isMultilingual
 * @property-read	string		$cssClassName
 * @property-read	integer		$showHeader
 * @property-read	integer		$originIsSystem
 * @property-read	integer		$packageID
 * @property-read	integer|null	$menuID
 * @property-read	integer		$linkPageID
 * @property-read	integer		$linkPageObjectID
 * @property-read	string		$externalURL
 * @property-read	mixed[]		$additionalData
 */
class Box extends DatabaseObject {
	/**
	 * image media object
	 * @var	ViewableMedia
	 */
	protected $image;
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'box';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'boxID';
	
	/**
	 * available box types
	 * @var	string[]
	 */
	public static $availableBoxTypes = ['text', 'html', 'tpl', 'system'];
	
	/**
	 * available box positions
	 * @var	string[]
	 */
	public static $availablePositions = ['hero', 'headerBoxes', 'top', 'sidebarLeft', 'contentTop', 'sidebarRight', 'contentBottom', 'bottom', 'footerBoxes', 'footer'];
	
	/**
	 * available menu positions
	 * @var	string[]
	 */
	public static $availableMenuPositions = ['top', 'sidebarLeft', 'sidebarRight', 'bottom', 'footer'];
	
	/**
	 * menu object
	 * @var	Menu
	 */
	protected $menu;
	
	/**
	 * box to page assignments
	 * @var	integer[]
	 */
	protected $pageIDs;
	
	/**
	 * box controller
	 * @var	\wcf\system\box\IBoxController
	 */
	protected $controller;
	
	/**
	 * box content grouped by language id
	 * @var	BoxContent[]
	 */
	public $boxContents;
	
	/**
	 * @inheritDoc
	 */
	public function __get($name) {
		$value = parent::__get($name);
		
		if ($value === null && isset($this->data['additionalData'][$name])) {
			$value = $this->data['additionalData'][$name];
		}
		
		return $value;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function handleData($data) {
		parent::handleData($data);
		
		// handle condition data
		$this->data['additionalData'] = @unserialize($data['additionalData']);
		if (!is_array($this->data['additionalData'])) {
			$this->data['additionalData'] = [];
		}
	}
	
	/**
	 * @var	IMenuPageHandler
	 */
	protected $linkPageHandler;
	
	/**
	 * page object
	 * @var	Page
	 */
	protected $linkPage;
	
	/**
	 * Returns true if the active user can delete this box.
	 * 
	 * @return	boolean
	 */
	public function canDelete() {
		if (WCF::getSession()->getPermission('admin.content.cms.canManageBox') && !$this->originIsSystem) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns the box's content.
	 *
	 * @return	BoxContent[]
	 */
	public function getBoxContents() {
		if ($this->boxContents === null) {
			$this->boxContents = [];
			
			$sql = "SELECT	*
				FROM	wcf" . WCF_N . "_box_content
				WHERE	boxID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->boxID]);
			while ($row = $statement->fetchArray()) {
				$this->boxContents[($row['languageID'] ?: 0)] = new BoxContent(null, $row);
			}
		}
		
		return $this->boxContents;
	}
	
	/**
	 * Returns the title of the box as set in the box content database table.
	 * 
	 * @return	string
	 */
	public function getBoxContentTitle() {
		$this->getBoxContents();
		if ($this->isMultilingual || $this->boxType == 'system') {
			if (isset($this->boxContents[WCF::getLanguage()->languageID])) {
				return $this->boxContents[WCF::getLanguage()->languageID]->title;
			}
		}
		else if (isset($this->boxContents[0])) {
			return $this->boxContents[0]->title;
		}
		
		return '';
	}
	
	/**
	 * Returns the title for the rendered version of this box.
	 * 
	 * @return	string
	 */
	public function getTitle() {
		if ($this->boxType == 'menu') {
			return $this->getMenu()->getTitle();
		}
		
		return $this->getBoxContentTitle();
	}
	
	/**
	 * Returns the content for the rendered version of this box.
	 * 
	 * @return	string
	 */
	public function getContent() {
		if ($this->boxType == 'system') {
			return $this->getController()->getContent();
		}
		else if ($this->boxType == 'menu') {
			return $this->getMenu()->getContent();
		}
		else if ($this->boxType == 'tpl') {
			return WCF::getTPL()->fetch($this->getTplName(WCF::getLanguage()->languageID), 'wcf', [], true);
		}
		
		$this->getBoxContents();
		$boxContent = null;
		if ($this->isMultilingual) {
			if (isset($this->boxContents[WCF::getLanguage()->languageID])) $boxContent = $this->boxContents[WCF::getLanguage()->languageID];
		}
		else {
			if (isset($this->boxContents[0])) $boxContent = $this->boxContents[0];
		}
		
		if ($boxContent !== null) {
			if ($this->boxType == 'text') {
				// assign embedded objects
				MessageEmbeddedObjectManager::getInstance()->setActiveMessage('com.woltlab.wcf.box.content', $boxContent->boxContentID);
				
				$processor = new HtmlOutputProcessor();
				$processor->process($boxContent->content, 'com.woltlab.wcf.box.content', $boxContent->boxContentID);
				
				return $processor->getHtml();
			}
			
			return $boxContent->content;
		}
		return '';
	}
	
	/**
	 * Returns the rendered version of this box.
	 * 
	 * @return	string
	 */
	public function render() {
		if (!$this->hasContent()) return ''; 
		
		WCF::getTPL()->assign([
			'box' => $this
		]);
		return WCF::getTPL()->fetch('__box');
	}
	
	/**
	 * Returns false if this box has no content.
	 * 
	 * @return	boolean
	 */
	public function hasContent() {
		if ($this->boxType == 'system') {
			return $this->getController()->hasContent();
		}
		else if ($this->boxType == 'menu') {
			return $this->getMenu()->hasContent();
		}
		
		$this->getBoxContents();
		$content = '';
		if ($this->isMultilingual) {
			if (isset($this->boxContents[WCF::getLanguage()->languageID])) $content = $this->boxContents[WCF::getLanguage()->languageID]->content;
		}
		else {
			if (isset($this->boxContents[0])) $content = $this->boxContents[0]->content;
		}
		
		return !empty($content);
	}
	
	/**
	 * Returns the box controller.
	 * 
	 * @return	IBoxController
	 */
	public function getController() {
		if ($this->controller === null && $this->objectTypeID) {
			$className = ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID)->className;
			
			$this->controller = new $className;
			$this->controller->setBox($this);
		}
		
		return $this->controller;
	}
	
	/**
	 * Returns the menu shown in the box.
	 * 
	 * @return	Menu
	 */
	public function getMenu() {
		if ($this->menu === null) {
			$this->menu = MenuCache::getInstance()->getMenuByID($this->menuID);
		}
		
		return $this->menu;
	}
	
	/**
	 * Returns the image of this box.
	 * 
	 * @return	ViewableMedia
	 */
	public function getImage() {
		if ($this->boxType == 'system') {
			return $this->getController()->getImage();
		}
		else if ($this->boxType == 'menu') {
			return null;
		}
		
		if ($this->image !== null) {
			return $this->image;
		}
		
		$this->getBoxContents();
		if ($this->isMultilingual) {
			if (isset($this->boxContents[WCF::getLanguage()->languageID]) && $this->boxContents[WCF::getLanguage()->languageID]->imageID) {
				$this->image = ViewableMedia::getMedia($this->boxContents[WCF::getLanguage()->languageID]->imageID);
			}
		}
		else if (isset($this->boxContents[0]) && $this->boxContents[0]->imageID) {
			$this->image = ViewableMedia::getMedia($this->boxContents[0]->imageID);
		}
		
		$this->image->setLinkParameters(['boxID' => $this->boxID]);
		
		return $this->image;
	}
	
	/**
	 * Returns true if this box has an image.
	 * 
	 * @return	boolean
	 */
	public function hasImage() {
		if ($this->boxType == 'system') {
			return $this->getController()->hasImage();
		}
		else if ($this->boxType == 'menu') {
			return false;
		}
		
		$this->getBoxContents();
		if ($this->isMultilingual) {
			return (isset($this->boxContents[WCF::getLanguage()->languageID]) && $this->boxContents[WCF::getLanguage()->languageID]->imageID);
		}
		
		return (isset($this->boxContents[0]) && $this->boxContents[0]->imageID);
	}
	
	/**
	 * Returns the URL of this box.
	 *
	 * @return	string
	 */
	public function getLink() {
		if ($this->boxType == 'system') {
			return $this->getController()->getLink();
		}
		else if ($this->boxType == 'menu') {
			return '';
		}
		
		if ($this->linkPageObjectID) {
			$handler = $this->getLinkPageHandler();
			if ($handler && $handler instanceof ILookupPageHandler) {
				return $handler->getLink($this->linkPageObjectID);
			}
		}
		
		if ($this->linkPageID) {
			return $this->getLinkPage()->getLink();
		}
		else {
			return $this->externalURL;
		}
	}
	
	/**
	 * Returns true if this box has a link.
	 *
	 * @return	boolean
	 */
	public function hasLink() {
		if ($this->boxType == 'system') {
			return $this->getController()->hasLink();
		}
		else if ($this->boxType == 'menu') {
			return false;
		}
		
		return ($this->linkPageID || !empty($this->externalURL));
	}
	
	/**
	 * Returns the IMenuPageHandler of the linked page.
	 *
	 * @return	IMenuPageHandler|null
	 * @throws	SystemException
	 */
	protected function getLinkPageHandler() {
		$page = $this->getLinkPage();
		if ($page !== null && $page->handler) {
			if ($this->linkPageHandler === null) {
				$className = $page->handler;
				$this->linkPageHandler = new $className;
				if (!($this->linkPageHandler instanceof IMenuPageHandler)) {
					throw new SystemException("Expected a valid handler implementing '" . IMenuPageHandler::class . "'.");
				}
			}
		}
		
		return $this->linkPageHandler;
	}
	
	/**
	 * Returns the page that is linked by this box.
	 *
	 * @return	Page|null
	 */
	public function getLinkPage() {
		if ($this->linkPage === null && $this->linkPageID) {
			$this->linkPage = PageCache::getInstance()->getPage($this->linkPageID);
		}
		
		return $this->linkPage;
	}
	
	/**
	 * Returns the template name of this box.
	 *
	 * @param	integer		$languageID
	 * @return	string
	 */
	public function getTplName($languageID = null) {
		if ($this->boxType == 'tpl') {
			if ($this->isMultilingual) {
				return '__cms_box_' . $this->boxID . '_' . $languageID;
			}
			
			return '__cms_box_' . $this->boxID;
		}
		
		return '';
	}
	
	/**
	 * Returns box to page assignments.
	 * 
	 * @return	integer[]
	 */
	public function getPageIDs() {
		if ($this->pageIDs === null) {
			$sql = "SELECT	pageID
				FROM	wcf" . WCF_N . "_box_to_page
				WHERE	boxID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->boxID]);
			
			$this->pageIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		}
		
		return $this->pageIDs;
	}
	
	/**
	 * Returns the conditions of the notice.
	 *
	 * @return	Condition[]
	 */
	public function getConditions() {
		/** @noinspection PhpUndefinedMethodInspection */
		if ($this->boxType === 'system' && $this->getController() instanceof IConditionBoxController && $this->getController()->getConditionDefinition()) {
			/** @noinspection PhpUndefinedMethodInspection */
			return ConditionHandler::getInstance()->getConditions($this->getController()->getConditionDefinition(), $this->boxID);
		}
		
		return [];
	}
	
	/**
	 * Returns true if this box is accessible by current user.
	 *
	 * @return	boolean
	 */
	public function isAccessible() {
		return SimpleAclResolver::getInstance()->canAccess('com.woltlab.wcf.box', $this->boxID);
	}
	
	/**
	 * Returns the box with the given idnetifier.
	 *
	 * @param	string		$identifier
	 * @return	Box
	 */
	public static function getBoxByIdentifier($identifier) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_box
			WHERE	identifier = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$identifier]);
		
		return $statement->fetchObject(self::class);
	}
	
	/**
	 * Returns the box with the given name.
	 *
	 * @param	string		$name
	 * @return	Box
	 */
	public static function getBoxByName($name) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_box
			WHERE	name = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$name]);
		
		return $statement->fetchObject(self::class);
	}
	
	/**
	 * Returns the box with the menu id.
	 *
	 * @param	int	$menuID
	 * @return	Box
	 */
	public static function getBoxByMenuID($menuID) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_box
			WHERE	menuID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$menuID]);
		
		return $statement->fetchObject(self::class);
	}
}
