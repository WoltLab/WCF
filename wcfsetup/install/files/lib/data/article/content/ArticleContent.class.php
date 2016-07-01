<?php
namespace wcf\data\article\content;
use wcf\data\article\Article;
use wcf\data\language\Language;
use wcf\data\DatabaseObject;
use wcf\data\ILinkableObject;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\language\LanguageFactory;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents an article content.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article\Content
 * @since	3.0
 *
 * @property-read	integer		$articleContentID
 * @property-read	integer		$articleID
 * @property-read	integer		$languageID
 * @property-read	string		$title
 * @property-read	string		$content
 * @property-read	string		$teaser
 * @property-read	integer		$imageID
 * @property-read	integer		$hasEmbeddedObjects
 */
class ArticleContent extends DatabaseObject implements ILinkableObject, IRouteController {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'article_content';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'articleContentID';
	
	/**
	 * article object
	 * @var Article
	 */
	protected $article;
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('Article', [
			'object' => $this,
			'forceFrontend' => true
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * Returns the article's unformatted content.
	 *
	 * @return      string
	 */
	public function getTeaser() {
		return $this->teaser;
	}
	
	/**
	 * Returns the article's formatted content.
	 *
	 * @return      string
	 */
	public function getFormattedTeaser() {
		if ($this->teaser) {
			return StringUtil::encodeHTML($this->teaser);
		}
		else {
			return StringUtil::truncateHTML(StringUtil::stripHTML($this->getFormattedContent()));
		}
	}
	
	/**
	 * Returns the article's formatted content.
	 *
	 * @return      string
	 */
	public function getFormattedContent() {
		// assign embedded objects
		MessageEmbeddedObjectManager::getInstance()->setActiveMessage('com.woltlab.wcf.article.content', $this->articleContentID);
		
		$processor = new HtmlOutputProcessor();
		$processor->process($this->content, 'com.woltlab.wcf.article.content', $this->articleContentID);
		
		return $processor->getHtml();
	}
	
	/**
	 * Returns article object.
	 * 
	 * @return Article
	 */
	public function getArticle() {
		if ($this->article === null) {
			$this->article = new Article($this->articleID);
		}
		
		return $this->article;
	}
	
	/**
	 * Returns the language of this article content as language object.
	 * 
	 * @return Language|null
	 */
	public function getLanguage() {
		if ($this->languageID) {
			return LanguageFactory::getInstance()->getLanguage($this->languageID);
		}
		
		return null;
	}
	
	/**
	 * Returns a certain article content.
	 * 
	 * @param       integer         $articleID
	 * @param       integer         $languageID
	 * @return      ArticleContent|null
	 */
	public static function getArticleContent($articleID, $languageID) {
		if ($languageID !== null) {
			$sql = "SELECT  *
				FROM    wcf" . WCF_N . "_article_content
				WHERE   articleID = ?
					AND languageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$articleID, $languageID]);
		}
		else {
			$sql = "SELECT  *
				FROM    wcf" . WCF_N . "_article_content
				WHERE   articleID = ?
					AND languageID IS NULL";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$articleID]);
		}
		
		if (($row = $statement->fetchSingleRow()) !== false) {
			return new ArticleContent(null, $row);
		}
		
		return null;
	}
}
