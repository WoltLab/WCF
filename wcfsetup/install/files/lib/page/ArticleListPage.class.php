<?php
namespace wcf\page;
use wcf\data\article\category\ArticleCategory;
use wcf\data\article\AccessibleArticleList;
use wcf\data\label\group\ViewableLabelGroup;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\label\LabelHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Shows a list of cms articles.
 *
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 * @since	3.0
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
	public $sortField = 'time';
	
	/**
	 * @inheritDoc
	 */
	public $sortOrder = ARTICLE_SORT_ORDER;
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = AccessibleArticleList::class;
	
	/**
	 * label filter
	 * @var	integer[]
	 */
	public $labelIDs = [];
	
	/**
	 * list of available label groups
	 * @var	ViewableLabelGroup[]
	 */
	public $labelGroups = [];
	
	/**
	 * controller name
	 * @var string
	 */
	public $controllerName = 'ArticleList';
	
	/**
	 * url parameters
	 * @var array
	 */
	public $controllerParameters = ['application' => 'wcf'];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		// read available label groups
		$this->labelGroups = ArticleCategory::getAccessibleLabelGroups('canViewLabel');
		if (!empty($this->labelGroups) && isset($_REQUEST['labelIDs']) && is_array($_REQUEST['labelIDs'])) {
			$this->labelIDs = $_REQUEST['labelIDs'];
			
			foreach ($this->labelIDs as $groupID => $labelID) {
				$isValid = false;
				
				// ignore zero-values
				if (!is_array($labelID) && $labelID) {
					if (isset($this->labelGroups[$groupID]) && ($labelID == -1 || $this->labelGroups[$groupID]->isValid($labelID))) {
						$isValid = true;
					}
				}
				
				if (!$isValid) {
					unset($this->labelIDs[$groupID]);
				}
			}
		}
		
		if (!empty($_POST)) {
			$labelParameters = '';
			if (!empty($this->labelIDs)) {
				foreach ($this->labelIDs as $groupID => $labelID) {
					$labelParameters .= 'labelIDs['.$groupID.']='.$labelID.'&';
				}
			}
			
			HeaderUtil::redirect(LinkHandler::getInstance()->getLink($this->controllerName, $this->controllerParameters, rtrim($labelParameters, '&')));
			exit;
		}
		
		$this->canonicalURL = LinkHandler::getInstance()->getLink('ArticleList', [], ($this->pageNo > 1 ? 'pageNo=' . $this->pageNo : ''));
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->applyFilters();
	}
	
	protected function applyFilters() {
		// filter by label
		if (!empty($this->labelIDs)) {
			$objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.label.object', 'com.woltlab.wcf.article')->objectTypeID;
			
			foreach ($this->labelIDs as $groupID => $labelID) {
				if ($labelID == -1) {
					$groupLabelIDs = LabelHandler::getInstance()->getLabelGroup($groupID)->getLabelIDs();
					
					if (!empty($groupLabelIDs)) {
						$this->objectList->getConditionBuilder()->add('article.articleID NOT IN (SELECT objectID FROM wcf'.WCF_N.'_label_object WHERE objectTypeID = ? AND labelID IN (?))', [
							$objectTypeID,
							$groupLabelIDs
						]);
					}
				}
				else {
					$this->objectList->getConditionBuilder()->add('article.articleID IN (SELECT objectID FROM wcf'.WCF_N.'_label_object WHERE objectTypeID = ? AND labelID = ?)', [
						$objectTypeID,
						$labelID
					]);
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'labelGroups' => $this->labelGroups,
			'labelIDs' => $this->labelIDs,
			'controllerName' => $this->controllerName,
			'controllerObject' => null
		]);
	}
}
