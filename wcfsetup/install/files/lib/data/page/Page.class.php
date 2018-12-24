<?php
namespace wcf\data\page;
use wcf\data\application\Application;
use wcf\data\page\content\PageContent;
use wcf\data\DatabaseObject;
use wcf\data\ILinkableObject;
use wcf\data\ITitledObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\data\TDatabaseObjectPermissions;
use wcf\system\acl\simple\SimpleAclResolver;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\builder\ApplicationCacheBuilder;
use wcf\system\cache\builder\RoutingCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents a page.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Page
 * @since	3.0
 * 
 * @property-read	integer		$pageID			        unique id of the page
 * @property-read	integer|null	$parentPageID		        id of the page's parent page or `null` if it has no parent page
 * @property-read	string		$identifier		        unique textual identifier of the page
 * @property-read	string		$name			        monolingual name of the page shown in the ACP
 * @property-read	string		$pageType		        type of the page, default types: `text`, `html`, `tpl` `system`
 * @property-read	integer		$isDisabled		        is `1` if the page is disabled and thus cannot be accessed, otherwise `0`
 * @property-read	integer		$isLandingPage		        is `1` if the page is the landing page, otherwise `0`
 * @property-read	integer		$isMultilingual		        is `1` if the page is available in different languages, otherwise `0`
 * @property-read	integer		$originIsSystem		        is `1` if the page has been delivered by a package, otherwise `0` (i.e. the page has been created in the ACP)
 * @property-read	integer		$packageID		        id of the package the which delivers the page or `1` if it has been created in the ACP
 * @property-read	integer		$applicationPackageID	        id of the package of the application the pages belongs to
 * @property-read	integer		$overrideApplicationPackageID	id of the package of the application that the page virtually belongs to
 * @property-read	string		$controller		        name of the page controller class
 * @property-read	string		$handler		        name of the page handler class for `system` pages or empty 
 * @property-read	string		$controllerCustomURL	        custom url of the page
 * @property-read	integer		$requireObjectID	        is `1` if the page requires an object id parameter, otherwise `0`
 * @property-read	integer		$hasFixedParent		        is `1` if the page's parent page cannot be changed, otherwise `0`
 * @property-read	integer		$lastUpdateTime		        timestamp at which the page has been updated the last time
 * @property-read	string		$cssClassName		        css class name(s) of the page
 * @property-read	string		$availableDuringOfflineMode     is `1` if the page is available during offline mode, otherwise `0`
 * @property-read	string		$allowSpidersToIndex            is `1` if the page is accessible for search spiders, otherwise `0`
 * @property-read	string		$excludeFromLandingPage         is `1` if the page can never be set as landing page, otherwise `0`
 * @property-read	string		$enableShareButtons             is `1` if the page should display share buttons, otherwise `0`
 * @property-read	string		$permissions		        comma separated list of user group permissions of which the active user needs to have at least one to access the page
 * @property-read	string		$options		        comma separated list of options of which at least one needs to be enabled for the page to be accessible
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
				$this->pageContents[$row['languageID'] ?: 0] = new PageContent(null, $row);
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
			$controllerParts = explode('\\', $this->controller);
			$controllerName = $controllerParts[count($controllerParts) - 1];
			$controllerName = preg_replace('/(page|action|form)$/i', '', $controllerName);
			
			$application = $controllerParts[0];
			if ($this->overrideApplicationPackageID) {
				$application = ApplicationHandler::getInstance()->getApplicationByID($this->overrideApplicationPackageID)->getAbbreviation();
			}
			
			return LinkHandler::getInstance()->getLink($controllerName, [
				'application' => $application,
				'forceFrontend' => true
			]);
		}
		else if ($this->applicationPackageID === null && $this->overrideApplicationPackageID === null) {
			// we cannot reliably generate a link for an orphaned page
			return '';
		}       
		
		return LinkHandler::getInstance()->getCmsLink($this->pageID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return PageCache::getInstance()->getPageTitle($this->pageID);
	}
	
	/**
	 * Returns shortened link for acp page list.
	 *
	 * @return	string
	 */
	public function getDisplayLink() {
		$link = preg_replace('~^https?://~', '', $this->getLink());
		
		return $link;
	}
	
	/**
	 * Returns the application of this page.
	 *
	 * @return Application
	 */
	public function getApplication() {
		return ApplicationHandler::getInstance()->getApplicationByID($this->overrideApplicationPackageID ?: $this->applicationPackageID);
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
		
		$sql = "UPDATE	wcf".WCF_N."_application
			SET	landingPageID = ?
			WHERE	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$this->pageID,
			1
		]);
		WCF::getDB()->commitTransaction();
		
		// reset caches to reflect new landing page
		ApplicationCacheBuilder::getInstance()->reset();
		RoutingCacheBuilder::getInstance()->reset();
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
	 * Returns the parsed template.
	 * 
	 * @param       PageContent     $pageContent    page content
	 * @return      string          parsed template
	 */
	public function getParsedTemplate(PageContent $pageContent) {
		return $pageContent->getParsedTemplate($this->getTplName($pageContent->languageID));
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
	 * Returns true if the share buttons are enabled and this is not a system-type page.
	 * 
	 * @return      bool
	 * @since       3.2
	 */
	public function showShareButtons() {
		return $this->enableShareButtons && $this->pageType !== 'system';
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
