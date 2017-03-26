<?php
namespace wcf\data\article\content;
use wcf\data\article\Article;
use wcf\data\language\Language;
use wcf\data\DatabaseObject;
use wcf\data\ILinkableObject;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\html\output\AmpHtmlOutputProcessor;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\language\LanguageFactory;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents an article content.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article\Content
 * @since	3.0
 *
 * @property-read	integer		$articleContentID	unique id of the article content
 * @property-read	integer		$articleID		id of the article the article content belongs to
 * @property-read	integer		$languageID		id of the article content's language
 * @property-read	string		$title			title of the article in the associated language
 * @property-read	string		$content		actual content of the article in the associated language
 * @property-read	string		$teaser			teaser of the article in the associated language or empty if no teaser exists
 * @property-read	integer|null	$imageID		id of the (image) media object used as article image for the associated language or `null` if no image is used
 * @property-read	integer		$hasEmbeddedObjects	is `1` if there are embedded objects in the article content, otherwise `0`
 */
class ArticleContent extends DatabaseObject implements ILinkableObject, IRouteController {
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
			$htmlInputProcessor = new HtmlInputProcessor();
			$htmlInputProcessor->processIntermediate($this->content);
			return StringUtil::encodeHTML(StringUtil::truncate($htmlInputProcessor->getTextContent(), 500));
		}
	}
	
	/**
	 * Returns the article's formatted content.
	 *
	 * @return      string
	 */
	public function getFormattedContent() {
		$processor = new HtmlOutputProcessor();
		$processor->process($this->content, 'com.woltlab.wcf.article.content', $this->articleContentID);
		
		return $processor->getHtml();
	}
	
	/**
	 * Returns the article's formatted content ready for use with Google AMP pages.
	 * 
	 * @return      string
	 */
	public function getAmpFormattedContent() {
		$processor = new AmpHtmlOutputProcessor();
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
	 * Returns the language of this article content or `null` if no language has been specified.
	 * 
	 * @return	Language|null
	 */
	public function getLanguage() {
		if ($this->languageID) {
			return LanguageFactory::getInstance()->getLanguage($this->languageID);
		}
		
		return null;
	}
	
	/**
	 * Returns a certain article content or `null` if it does not exist.
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
