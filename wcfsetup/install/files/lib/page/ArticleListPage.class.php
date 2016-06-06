<?php
namespace wcf\page;
use wcf\data\article\AccessibleArticleList;
use wcf\system\request\LinkHandler;

/**
 * Shows a list of cms articles.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 * @since       2.2
 */
class ArticleListPage extends MultipleLinkPage {
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = ARTICLES_PER_PAGE;
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_ARTICLE'];
	
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'article.time DESC';
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = AccessibleArticleList::class;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->canonicalURL = LinkHandler::getInstance()->getLink('ArticleList', [], ($this->pageNo > 1 ? 'pageNo=' . $this->pageNo : ''));
	}
}
