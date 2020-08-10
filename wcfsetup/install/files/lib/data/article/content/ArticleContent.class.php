<?php
namespace wcf\data\article\content;
use wcf\data\article\Article;
use wcf\data\language\Language;
use wcf\data\DatabaseObject;
use wcf\data\ILinkableObject;
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
 * @copyright	2001-2019 WoltLab GmbH
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
 * @property-read	integer|null	$teaserImageID          id of the (image) media object used as article teaser image for the associated language or `null` if no image is used                                      
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
	 * Returns the article's unformatted teaser.
	 *
	 * @return      string
	 */
	public function getTeaser() {
		return $this->teaser;
	}
	
	/**
	 * Returns the article's formatted teaser.
	 *
	 * @return      string
	 */
	public function getFormattedTeaser() {
		if ($this->teaser) {
			return nl2br(StringUtil::encodeHTML($this->teaser), false);
		}
		else {
			$htmlOutputProcessor = new HtmlOutputProcessor();
			$htmlOutputProcessor->setOutputType('text/plain');
			$htmlOutputProcessor->enableUgc = false;
			$htmlOutputProcessor->process($this->content, 'com.woltlab.wcf.article.content', $this->articleContentID, false, $this->languageID);
			
			return nl2br(StringUtil::encodeHTML(StringUtil::truncate($htmlOutputProcessor->getHtml(), 500)), false);
		}
	}
	
	/**
	 * Returns the article's formatted content.
	 *
	 * @return      string
	 */
	public function getFormattedContent() {
		$processor = new HtmlOutputProcessor();
		$processor->enableUgc = false;
		$processor->process($this->content, 'com.woltlab.wcf.article.content', $this->articleContentID, false, $this->languageID);
		
		return $processor->getHtml();
	}
	
	/**
	 * Returns the article's formatted content ready for use with Google AMP pages.
	 * 
	 * @return      string
	 */
	public function getAmpFormattedContent() {
		$processor = new AmpHtmlOutputProcessor();
		$processor->enableUgc = false;
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
	 * Returns a version of this message optimized for use in emails.
	 *
	 * @param	string	$mimeType	Either 'text/plain' or 'text/html'
	 * @return	string
	 * @since       5.2
	 */
	public function getMailText($mimeType = 'text/plain') {
		switch ($mimeType) {
			case 'text/plain':
				$processor = new HtmlOutputProcessor();
				$processor->setOutputType('text/plain');
				$processor->enableUgc = false;
				$processor->process($this->content, 'com.woltlab.wcf.article.content', $this->articleContentID);
				
				return $processor->getHtml();
			case 'text/html':
				// parse and return message
				$processor = new HtmlOutputProcessor();
				$processor->setOutputType('text/simplified-html');
				$processor->enableUgc = false;
				$processor->process($this->content, 'com.woltlab.wcf.article.content', $this->articleContentID);
				
				return $processor->getHtml();
		}
		
		throw new \LogicException('Unreachable');
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
