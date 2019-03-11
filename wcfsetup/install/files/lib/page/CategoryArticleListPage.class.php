<?php
namespace wcf\page;
use wcf\data\article\category\ArticleCategory;
use wcf\data\article\CategoryArticleList;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\page\PageLocationManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows a list of cms articles in a certain category.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 * @since	3.0 
 */
class CategoryArticleListPage extends ArticleListPage {
	/**
	 * category the listed articles belong to
	 * @var	ArticleCategory
	 */
	public $category;
	
	/**
	 * id of the category the listed articles belong to
	 * @var	integer
	 */
	public $categoryID = 0;
	
	/**
	 * @inheritDoc
	 */
	public $controllerName = 'CategoryArticleList';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		if (isset($_REQUEST['id'])) $this->categoryID = intval($_REQUEST['id']);
		$this->category = ArticleCategory::getCategory($this->categoryID);
		if ($this->category === null) {
			throw new IllegalLinkException();
		}
		$this->controllerParameters['object'] = $this->category;
		parent::readParameters();
		
		$this->canonicalURL = LinkHandler::getInstance()->getLink('CategoryArticleList', [
			'object' => $this->category
		], ($this->pageNo > 1 ? 'pageNo=' . $this->pageNo : ''));
		
		if ($this->category->sortField) {
			if ($this->category->sortField === 'title') {
				$this->sortField = 'article_content.title';
				$this->sortOrder = $this->category->sortOrder;
			}
			else {
				$this->sortField = $this->category->sortField;
				$this->sortOrder = $this->category->sortOrder;
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkPermissions() {
		parent::checkPermissions();
		
		if (!$this->category->isAccessible()) {
			throw new PermissionDeniedException();
		}
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		$this->objectList = new CategoryArticleList($this->categoryID, true);
		if ($this->category->sortField === 'title') {
			$this->objectList->sqlJoins .= ' LEFT JOIN wcf' . WCF_N . '_article_content article_content ON (article_content.articleID = article.articleID AND (
				article_content.languageID IS NULL
				OR article_content.languageID = ' . WCF::getLanguage()->languageID . '
			))';
		}
		
		$this->applyFilters();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// set location
		foreach ($this->category->getParentCategories() as $parentCategory) {
			PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.CategoryArticleList', $parentCategory->categoryID, $parentCategory);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'categoryID' => $this->categoryID,
			'category' => $this->category,
			'controllerObject' => $this->category
		]);
	}
}
