<?php
namespace wcf\data\page;
use wcf\data\DatabaseObject;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents a page.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page
 * @category	Community Framework
 * @since	2.2
 */
class Page extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'page';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'pageID';
	
	/**
	 * @var \wcf\system\page\handler\IMenuPageHandler
	 */
	protected $pageHandler;
	
	/**
	 * Returns true if the active user can delete this page.
	 * 
	 * @return boolean
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
	 * @return boolean
	 */
	public function canDisable() {
		if (WCF::getSession()->getPermission('admin.content.cms.canManagePage') && !$this->originIsSystem && !$this->isLandingPage) {
			return true;
		}
			
		return false;
	}
	
	/**
	 * Returns the page content.
	 * 
	 * @return      array           content data
	 */
	public function getPageContent() {
		$content = [];
		
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_page_content
			WHERE   pageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->pageID]);
		while ($row = $statement->fetchArray()) {
			$content[($row['languageID'] ?: 0)] = [
				'title' => $row['title'],
				'content' => $row['content'],
				'metaDescription' => $row['metaDescription'],
				'metaKeywords' => $row['metaKeywords'],
				'customURL' => $row['customURL']
			];
		}
		
		return $content;
	}
	
	/**
	 * Returns content for a single language, passing `null` for `$languageID` is undefined
	 * for multilingual pages.
	 * 
	 * @param       integer         $languageID     language id or `null` if there are no localized versions
	 * @return      string[]        page content data
	 * @throws      \wcf\system\database\DatabaseException
	 */
	public function getPageContentByLanguage($languageID = null) {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("pageID = ?", [$this->pageID]);
		if ($this->isMultilingual) $conditions->add("languageID = ?", [$languageID]);
		else $conditions->add("languageID IS NULL");
		
		$sql = "SELECT  *
			FROM    wcf".WCF_N."_page_content
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute($conditions->getParameters());
		$row = $statement->fetchSingleRow();
		
		return $row ?: [];
	}
	
	/**
	 * Returns the page URL.
	 * 
	 * @return      string
	 */
	public function getURL() {
		if ($this->controller) {
			// todo
			$controllerParts = explode('\\', $this->controller);
			$controllerName = $controllerParts[count($controllerParts) - 1];
			$controllerName = preg_replace('/(page|action|form)$/i', '', $controllerName);
			
			return LinkHandler::getInstance()->getLink($controllerName, [
				'application' => $controllerParts[0]
			]);
		}
		else {
			return LinkHandler::getInstance()->getCmsLink($this->pageID);
		}	
	}
	
	/**
	 * Returns the associated page handler or null.
	 * 
	 * @return      \wcf\system\page\handler\IMenuPageHandler|null
	 */
	public function getHandler() {
		if ($this->handler) {
			$this->pageHandler = new $this->handler();
		}
		
		return $this->pageHandler;
	}
	
	/**
	 * Returns the page's internal name.
	 * 
	 * @return string
	 */
	public function __toString() {
		return $this->name;
	}
	
	/**
	 * Returns the page with the given identifier.
	 * 
	 * @param       string          $identifier     unique page identifier
	 * @return      Page
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
