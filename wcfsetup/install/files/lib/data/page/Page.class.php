<?php
namespace wcf\data\page;
use wcf\data\page\content\PageContent;
use wcf\data\DatabaseObject;
use wcf\data\ILinkableObject;
use wcf\data\ITitledObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\data\TDatabaseObjectPermissions;
use wcf\system\acl\simple\SimpleAclResolver;
use wcf\system\application\ApplicationHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents a page.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Page
 * @since	3.0
 *
 * @property-read	integer		$pageID
 * @property-read	integer|null	$parentPageID
 * @property-read	string		$identifier
 * @property-read	string		$name
 * @property-read	string		$pageType
 * @property-read	integer		$isDisabled
 * @property-read	integer		$isLandingPage
 * @property-read	integer		$isMultilingual
 * @property-read	integer		$originIsSystem
 * @property-read	integer		$packageID
 * @property-read	integer		$applicationPackageID
 * @property-read	string		$controller
 * @property-read	string		$handler
 * @property-read	string		$controllerCustomURL
 * @property-read	integer		$requireObjectID
 * @property-read	integer		$lastUpdateTime
 * @property-read	string		$permissions
 * @property-read	string		$options
 */
class Page extends DatabaseObject implements ILinkableObject, ITitledObject {
	use TDatabaseObjectOptions;
	use TDatabaseObjectPermissions;
	
	/**
	 * available page types
	 * @var	string[]
	 */
	public static $availablePageTypes = ['text', 'html', 'tpl', 'system'];
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'page';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'pageID';
	
	/**
	 * @var	\wcf\system\page\handler\IMenuPageHandler
	 */
	protected $pageHandler;
	
	/**
	 * box to page assignments
	 * @var integer[]
	 */
	protected $boxIDs;
	
	/**
	 * page content grouped by language id
	 * @var	PageContent[]
	 */
	public $pageContents;
	
	/**
	 * Returns true if the active user can delete this page.
	 * 
	 * @return	boolean
	 */
	public function canDelete() {
		if (WCF::getSession()->getPermission('admin.content.cms.canManagePage') && !$this->originIsSystem && !$this->isLandingPage) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns true if the active user can disable this page.
	 *
	 * @return	boolean
	 */
	public function canDisable() {
		if (WCF::getSession()->getPermission('admin.content.cms.canManagePage') && (!$this->originIsSystem || $this->pageType != 'system') && !$this->isLandingPage) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns the page's content.
	 *
	 * @return	PageContent[]
	 */
	public function getPageContents() {
		if ($this->pageContents === null) {
			$this->pageContents = [];
			
			$sql = "SELECT	*
				FROM	wcf" . WCF_N . "_page_content
				WHERE	pageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->pageID]);
			while ($row = $statement->fetchArray()) {
				$this->pageContents[($row['languageID'] ?: 0)] = new PageContent(null, $row);
			}
		}
		
		return $this->pageContents;
	}
	
	/**
	 * Returns content for a single language, passing `null` for `$languageID` is undefined
	 * for multilingual pages.
	 * 
	 * @param	integer		$languageID	language id or `null` if there are no localized versions
	 * @return	PageContent|null        	page content data
	 */
	public function getPageContentByLanguage($languageID = null) {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("pageID = ?", [$this->pageID]);
		if ($this->isMultilingual) $conditions->add("languageID = ?", [$languageID]);
		else $conditions->add("languageID IS NULL");
		
		$sql = "SELECT  *
			FROM	wcf".WCF_N."_page_content
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute($conditions->getParameters());
		$row = $statement->fetchSingleRow();
		if ($row !== false) return new PageContent(null, $row);
		
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		if ($this->controller) {
			// todo
			$controllerParts = explode('\\', $this->controller);
			$controllerName = $controllerParts[count($controllerParts) - 1];
			$controllerName = preg_replace('/(page|action|form)$/i', '', $controllerName);
			
			return LinkHandler::getInstance()->getLink($controllerName, [
				'application' => $controllerParts[0],
				'forceFrontend' => true
			]);
		}
		else {
			return LinkHandler::getInstance()->getCmsLink($this->pageID);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$title = PageCache::getInstance()->getPageTitle($this->pageID);
		
		return $title;
	}
	
	/**
	 * Returns shortened link for acp page list.
	 *
	 * @return	string
	 */
	public function getDisplayLink() {
		return preg_replace('~^https?://~', '', $this->getLink());
	}
	
	/**
	 * Returns the application of this page.
	 *
	 * @return	\wcf\data\application\Application
	 */
	public function getApplication() {
		return ApplicationHandler::getInstance()->getApplicationByID($this->applicationPackageID);
	}
	
	/**
	 * Returns the associated page handler or null.
	 * 
	 * @return	\wcf\system\page\handler\IMenuPageHandler|null
	 */
	public function getHandler() {
		if ($this->pageHandler === null && $this->handler) {
			$this->pageHandler = new $this->handler();
		}
		
		return $this->pageHandler;
	}
	
	/**
	 * Returns false if this page should be hidden from menus, but does not control the accessibility
	 * of the page itself.
	 *
	 * @return	boolean		false if the page should be hidden from menus
	 */
	public function isVisible() {
		if ($this->isDisabled) return false;
		if (!$this->validateOptions()) return false;
		if (!$this->validatePermissions()) return false;
		
		return true;
	}
	
	/**
	 * Returns true if this page is accessible by current user.
	 *
	 * @return	boolean
	 */
	public function isAccessible() {
		return SimpleAclResolver::getInstance()->canAccess('com.woltlab.wcf.page', $this->pageID);
	}
	
	/**
	 * Sets the current page as landing page.
	 * 
	 * @throws	SystemException
	 */
	public function setAsLandingPage() {
		if ($this->requireObjectID) {
			throw new SystemException('Pages requiring an object id cannot be set as landing page.');
		}
		
		WCF::getDB()->beginTransaction();
		// unmark existing landing page
		$sql = "UPDATE	wcf".WCF_N."_page
			SET	isLandingPage = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			0
		]);
		
		// set current page as landing page
		$sql = "UPDATE	wcf".WCF_N."_page
			SET	isLandingPage = ?
			WHERE	pageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			1,
			$this->pageID
		]);
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * Returns the page's internal name.
	 *
	 * @return	string
	 */
	public function __toString() {
		return $this->name;
	}
	
	/**
	 * Returns box to page assignments.
	 *
	 * @return	integer[]
	 */
	public function getBoxIDs() {
		if ($this->boxIDs === null) {
			$sql = "SELECT	boxID
				FROM	wcf" . WCF_N . "_box_to_page
				WHERE	pageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->pageID]);
			$this->boxIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		}
		
		return $this->boxIDs;
	}
	
	/**
	 * Returns the template name of this page.
	 * 
	 * @param	integer		$languageID
	 * @return	string
	 */
	public function getTplName($languageID = null) {
		if ($this->pageType == 'tpl') {
			if ($this->isMultilingual) {
				return '__cms_page_' . $this->pageID . '_' . $languageID;
			}
			
			return '__cms_page_' . $this->pageID;
		}
		
		return '';
	}
	
	/**
	 * Returns the languages of this page.
	 * 
	 * @return PageLanguage[]
	 */
	public function getPageLanguages() {
		$pageLanguages = [];
		if ($this->isMultilingual) {
			$sql = "SELECT  languageID
				FROM    wcf" . WCF_N . "_page_content
				WHERE   pageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->pageID]);
			while ($languageID = $statement->fetchColumn()) {
				$pageLanguages[] = new PageLanguage($this->pageID, $languageID);
			}
		}
		
		return $pageLanguages;
	}
	
	/**
	 * Returns the page with the given identifier.
	 * 
	 * @param	string		$identifier	unique page identifier
	 * @return	Page
	 */
	public static function getPageByIdentifier($identifier) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_page
			WHERE	identifier = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$identifier]);
		
		return $statement->fetchObject(self::class);
	}
	
	/**
	 * Returns the page with the given name.
	 * 
	 * @param	string		$name
	 * @return	Page
	 */
	public static function getPageByName($name) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_page
			WHERE	name = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$name]);
		
		return $statement->fetchObject(self::class);
	}
}
