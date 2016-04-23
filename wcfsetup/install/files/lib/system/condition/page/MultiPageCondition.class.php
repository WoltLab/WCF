<?php
namespace wcf\system\condition\page;
use wcf\data\condition\Condition;
use wcf\data\page\PageCache;
use wcf\data\page\PageNode;
use wcf\data\page\PageNodeTree;
use wcf\system\condition\AbstractMultiSelectCondition;
use wcf\system\condition\IContentCondition;
use wcf\system\exception\UserInputException;
use wcf\system\request\RequestHandler;

/**
 * Condition implementation for selecting multiple pages.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition.page
 * @category	Community Framework
 * @deprecated	since 2.2
 */
class MultiPageCondition extends AbstractMultiSelectCondition implements IContentCondition {
	/**
	 * @inheritDoc
	 */
	protected $description = 'wcf.global.multiSelect';
	
	/**
	 * @inheritDoc
	 */
	protected $fieldName = 'pageIDs';
	
	/**
	 * @inheritDoc
	 */
	protected $label = 'wcf.page.requestedPage';
	
	/**
	 * @inheritDoc
	 */
	protected function getFieldElement() {
		$pageNodes = (new PageNodeTree())->getNodeList();
		
		$fieldElement = '<select name="'.$this->fieldName.'[]" id="'.$this->fieldName.'" multiple="multiple" size="10">';
		/** @var PageNode $pageNode */
		foreach ($pageNodes as $pageNode) {
			$fieldElement .= '<option value="'.$pageNode->getPage()->pageID.'">'.($pageNode->getDepth() > 1 ? str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $pageNode->getDepth() - 1) : '').$pageNode->getPage()->name.'</option>';
		}
		$fieldElement .= "</select>";
		
		return $fieldElement;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getOptions() {
		return [];
	}
	
	/**
	 * @inheritDoc
	 */
	public function showContent(Condition $condition) {
		$pageID = RequestHandler::getInstance()->getActiveRequest()->getPageID();
		if ($pageID && $condition->pageIDs && is_array($condition->pageIDs)) {
			return in_array($pageID, $condition->pageIDs);
		}
		
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		foreach ($this->fieldValue as $value) {
			if (PageCache::getInstance()->getPage($value) === null) {
				$this->errorMessage = 'wcf.global.form.error.noValidSelection';
				
				throw new UserInputException($this->fieldName, 'noValidSelection');
			}
		}
	}
}
