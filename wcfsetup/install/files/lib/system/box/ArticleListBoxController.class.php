<?php
namespace wcf\system\box;
use wcf\data\article\AccessibleArticleList;
use wcf\system\WCF;

/**
 * Box controller for a list of articles.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Box
 * @since	3.0
 */
class ArticleListBoxController extends AbstractDatabaseObjectListBoxController {
	/**
	 * @inheritDoc
	 */
	protected $supportedPositions = ['sidebarLeft', 'sidebarRight', 'contentTop', 'contentBottom', 'top', 'bottom', 'footerBoxes'];
	
	/**
	 * @inheritDoc
	 */
	protected $sortFieldLanguageItemPrefix = 'wcf.article';
	
	/**
	 * @inheritDoc
	 */
	public $defaultLimit = 3;
	
	/**
	 * @inheritDoc
	 */
	protected $conditionDefinition = 'com.woltlab.wcf.box.articleList.condition';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = [
		'time',
		'comments',
		'views'
	];
	
	/**
	 * @inheritDoc
	 */
	public function __construct() {
		if (!empty($this->validSortFields) && MODULE_LIKE) {
			$this->validSortFields[] = 'cumulativeLikes';
		}
		
		parent::__construct();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getObjectList() {
		$objectList = new AccessibleArticleList();
		
		switch ($this->sortField) {
			case 'comments':
				$objectList->getConditionBuilder()->add('article.comments > ?', [0]);
				break;
			case 'views':
				$objectList->getConditionBuilder()->add('article.views > ?', [0]);
				break;
		}
		
		return $objectList;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getTemplate() {
		return WCF::getTPL()->fetch('boxArticleList', 'wcf', [
			'boxArticleList' => $this->objectList,
			'boxSortField' => $this->sortField,
			'boxPosition' => $this->box->position
		]);
	}
}
