<?php
namespace wcf\acp\page;
use wcf\data\article\ArticleList;
use wcf\data\article\ViewableArticleList;
use wcf\data\category\CategoryNodeTree;
use wcf\data\user\User;
use wcf\page\SortablePage;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows a list of cms articles.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * @since	3.0
 *
 * @property	ArticleList	$objectList
 */
class ArticleListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.article.list';
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = ViewableArticleList::class;
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_ARTICLE'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.article.canManageArticle'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'time';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['articleID', 'title', 'time', 'views', 'comments'];
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = 50;
	
	/**
	 * category id
	 * @var integer
	 */
	public $categoryID = 0;
	
	/**
	 * name
	 * @var	string
	 */
	public $username = '';
	
	/**
	 * title
	 * @var	string
	 */
	public $title = '';
	
	/**
	 * content
	 * @var	string
	 */
	public $content = '';
	
	/**
	 * display 'Add Article' dialog on load
	 * @var integer
	 */
	public $showArticleAddDialog = 0;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['categoryID'])) $this->categoryID = intval($_REQUEST['categoryID']);
		if (!empty($_REQUEST['username'])) $this->username = StringUtil::trim($_REQUEST['username']);
		if (!empty($_REQUEST['title'])) $this->title = StringUtil::trim($_REQUEST['title']);
		if (!empty($_REQUEST['content'])) $this->content = StringUtil::trim($_REQUEST['content']);
		if (!empty($_REQUEST['showArticleAddDialog'])) $this->showArticleAddDialog = 1;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		if ($this->categoryID) {
			$this->objectList->getConditionBuilder()->add('article.categoryID = ?', [$this->categoryID]);
		}
		if (!empty($this->username)) {
			$user = User::getUserByUsername($this->username);
			if ($user->userID) $this->objectList->getConditionBuilder()->add('article.userID = ?', [$user->userID]);
			else $this->objectList->getConditionBuilder()->add('1=0');
		}
		if (!empty($this->title)) {
			$this->objectList->getConditionBuilder()->add('article.articleID IN (SELECT articleID FROM wcf'.WCF_N.'_article_content WHERE title LIKE ?)', ['%'.$this->title.'%']);
		}
		if (!empty($this->content)) {
			$this->objectList->getConditionBuilder()->add('article.articleID IN (SELECT articleID FROM wcf'.WCF_N.'_article_content WHERE content LIKE ?)', ['%'.$this->content.'%']);
		}
		
		$this->objectList->sqlSelects = "(SELECT title FROM wcf".WCF_N."_article_content WHERE articleID = article.articleID AND (languageID IS NULL OR languageID = ".WCF::getLanguage()->languageID.") LIMIT 1) AS title";
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'categoryID' => $this->categoryID,
			'username' => $this->username,
			'title' => $this->title,
			'content' => $this->content,
			'showArticleAddDialog' => $this->showArticleAddDialog,
			'categoryNodeList' => (new CategoryNodeTree('com.woltlab.wcf.article.category'))->getIterator()
		]);
	}
}
