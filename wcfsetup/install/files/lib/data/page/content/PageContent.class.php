<?php
namespace wcf\data\page\content;
use wcf\data\DatabaseObject;
use wcf\data\ILinkableObject;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\html\simple\HtmlSimpleParser;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents a page content.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Page\Content
 * @since	3.0
 *
 * @property-read	integer		$pageContentID		unique id of the page content
 * @property-read	integer		$pageID			id of the page the page content belongs to
 * @property-read	integer		$languageID		id of the page content's language
 * @property-read	string		$title			title of the page in the associated language
 * @property-read	string		$content		actual content of the page in the associated language
 * @property-read	string		$metaDescription	meta description of the page in the associated language
 * @property-read	string		$metaKeywords		meta keywords of the page in the associated language
 * @property-read	string		$customURL		custom url of the page in the associated language
 * @property-read	integer		$hasEmbeddedObjects	is `1` if the page content contains embedded objects, otherwise `0`
 */
class PageContent extends DatabaseObject implements ILinkableObject {
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
		MessageEmbeddedObjectManager::getInstance()->loadObjects('com.woltlab.wcf.page.content', [$this->pageContentID]);
		
		$processor = new HtmlOutputProcessor();
		$processor->enableUgc = false;
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
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getCmsLink($this->pageID, $this->languageID);
	}
}
