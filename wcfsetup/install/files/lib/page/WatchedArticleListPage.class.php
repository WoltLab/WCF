<?php
namespace wcf\page;
use wcf\data\article\category\ArticleCategory;
use wcf\data\article\CategoryArticleList;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\LinkHandler;

/**
 * Shows a list of articles in watched categories.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 * @since       5.2
 */
class WatchedArticleListPage extends ArticleListPage {
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * @inheritDoc
	 */
	public $controllerName = 'WatchedArticleList';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (empty(ArticleCategory::getSubscribedCategoryIDs())) {
			throw new IllegalLinkException();
		}
		
		$this->canonicalURL = LinkHandler::getInstance()->getLink('WatchedArticleList', $this->controllerParameters, ($this->pageNo > 1 ? 'pageNo=' . $this->pageNo : ''));
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		$this->objectList = new CategoryArticleList(ArticleCategory::getSubscribedCategoryIDs());
		
		$this->applyFilters();
	}
}
