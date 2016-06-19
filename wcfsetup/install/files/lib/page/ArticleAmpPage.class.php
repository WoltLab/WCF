<?php
namespace wcf\page;
use wcf\data\article\content\ViewableArticleContent;
use wcf\data\article\ArticleEditor;
use wcf\data\article\ViewableArticle;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\page\PageLocationManager;
use wcf\system\WCF;

/**
 * Shows the amp version of an article.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 * @since	3.0
 */
class ArticleAmpPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $templateName = 'ampArticle';
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_ARTICLE'];
	
	/**
	 * article content id
	 * @var	integer
	 */
	public $articleContentID = 0;
	
	/**
	 * article content object
	 * @var	ViewableArticleContent
	 */
	public $articleContent;
	
	/**
	 * article object
	 * @var	ViewableArticle
	 */
	public $article;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->articleContentID = intval($_REQUEST['id']);
		$this->articleContent = ViewableArticleContent::getArticleContent($this->articleContentID);
		if ($this->articleContent === null) {
			throw new IllegalLinkException();
		}
		$this->article = ViewableArticle::getArticle($this->articleContent->articleID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkPermissions() {
		parent::checkPermissions();
		
		if (!$this->article->canRead()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// update view count
		$articleEditor = new ArticleEditor($this->article->getDecoratedObject());
		$articleEditor->updateCounters([
			'views' => 1
		]);
		
		// set location
		PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.CategoryArticleList', $this->article->categoryID, $this->article->getCategory());
		foreach ($this->article->getCategory()->getParentCategories() as $parentCategory) {
			PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.CategoryArticleList', $parentCategory->categoryID, $parentCategory);
		}
		PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.ArticleList');
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'articleContentID' => $this->articleContentID,
			'articleContent' => $this->articleContent,
			'article' => $this->article,
			'category' => $this->article->getCategory(),
			'regularCanonicalURL' => $this->articleContent->getLink()
		]);
	}
}
