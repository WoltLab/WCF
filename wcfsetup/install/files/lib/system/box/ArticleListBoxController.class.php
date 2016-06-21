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
	protected $supportedPositions = ['sidebarLeft', 'sidebarRight', 'contentTop', 'contentBottom', 'top', 'bottom'];
	
	/**
	 * @inheritDoc
	 */
	protected $sortFieldLanguageItemPrefix = 'wcf.article';
	
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
		return new AccessibleArticleList();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getTemplate() {
		$templateName = 'boxArticleList';
		if ($this->box->position === 'sidebarLeft' || $this->box->position === 'sidebarRight') {
			$templateName = 'boxArticleListSidebar';
		}
		
		return WCF::getTPL()->fetch($templateName, 'wcf', [
			'boxArticleList' => $this->objectList,
			'boxSortField' => $this->sortField
		]);
	}
}
