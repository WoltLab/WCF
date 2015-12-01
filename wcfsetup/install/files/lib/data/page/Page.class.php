<?php
namespace wcf\data\page;
use wcf\data\DatabaseObject;
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
	 * @return array
	 */
	public function getPageContent() {
		$content = array();
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_page_content
			WHERE	pageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->pageID));
		while ($row = $statement->fetchArray()) {
			$content[($row['languageID'] ?: 0)] = array(
				'title' => $row['title'],
				'content' => $row['content'],
				'metaDescription' => $row['metaDescription'],
				'metaKeywords' => $row['metaKeywords'],
				'customURL' => $row['customURL']
			);
		}
		
		return $content;
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
		$statement->execute(array($name));
		$row = $statement->fetchArray();
		if ($row !== false) return new Page(null, $row);
		
		return null;
	}
}
