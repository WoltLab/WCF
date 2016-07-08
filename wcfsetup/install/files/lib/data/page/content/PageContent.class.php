<?php
namespace wcf\data\page\content;
use wcf\data\DatabaseObject;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\html\simple\HtmlSimpleParser;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\template\plugin\SimpledEmbeddedObjectPrefilterTemplatePlugin;
use wcf\system\WCF;

/**
 * Represents a page content.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Page\Content
 * @since	3.0
 *
 * @property-read	integer		$pageContentID
 * @property-read	integer		$pageID
 * @property-read	integer		$languageID
 * @property-read	string		$title
 * @property-read	string		$content
 * @property-read	string		$metaDescription
 * @property-read	string		$metaKeywords
 * @property-read	string		$customURL
 * @property-read	integer		$hasEmbeddedObjects
 */
class PageContent extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'page_content';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'pageContentID';
	
	/**
	 * Returns the page's formatted content.
	 *
	 * @return      string
	 */
	public function getFormattedContent() {
		// assign embedded objects
		MessageEmbeddedObjectManager::getInstance()->setActiveMessage('com.woltlab.wcf.page.content', $this->pageContentID);
		MessageEmbeddedObjectManager::getInstance()->loadObjects('com.woltlab.wcf.page.content', [$this->pageContentID]);
		
		$processor = new HtmlOutputProcessor();
		$processor->process($this->content, 'com.woltlab.wcf.page.content', $this->pageContentID);
		
		return $processor->getHtml();
	}
	
	/**
	 * Parses simple placeholders embedded in raw html.
	 * 
	 * @return      string          parsed content
	 */
	public function getParsedContent() {
		MessageEmbeddedObjectManager::getInstance()->loadObjects('com.woltlab.wcf.page.content', [$this->pageContentID]);
		
		return HtmlSimpleParser::getInstance()->replaceTags('com.woltlab.wcf.page.content', $this->pageContentID, $this->content);
	}
	
	/**
	 * Parses simple placeholders embedded in HTML with template scripting.
	 * 
	 * @param       string          $templateName           content template name
	 * @return      string          parsed template
	 */
	public function getParsedTemplate($templateName) {
		MessageEmbeddedObjectManager::getInstance()->loadObjects('com.woltlab.wcf.page.content', [$this->pageContentID]);
		HtmlSimpleParser::getInstance()->setContext('com.woltlab.wcf.page.content', $this->pageContentID);
		
		WCF::getTPL()->registerPrefilter(['simpleEmbeddedObject']);
		
		$returnValue = WCF::getTPL()->fetch($templateName);
		
		WCF::getTPL()->removePrefilter('simpleEmbeddedObject');
		
		return $returnValue;
	}
	
	/**
	 * Returns a certain page content.
	 *
	 * @param       integer         $pageID
	 * @param       integer         $languageID
	 * @return      PageContent|null
	 */
	public static function getPageContent($pageID, $languageID) {
		if ($languageID !== null) {
			$sql = "SELECT  *
				FROM    wcf" . WCF_N . "_page_content
				WHERE   pageID = ?
					AND languageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$pageID, $languageID]);
		}
		else {
			$sql = "SELECT  *
				FROM    wcf" . WCF_N . "_page_content
				WHERE   pageID = ?
					AND languageID IS NULL";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$pageID]);
		}
		
		if (($row = $statement->fetchSingleRow()) !== false) {
			return new PageContent(null, $row);
		}
		
		return null;
	}
}
