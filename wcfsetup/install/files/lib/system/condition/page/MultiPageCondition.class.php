<?php
namespace wcf\system\condition\page;
use wcf\data\condition\Condition;
use wcf\data\page\PageCache;
use wcf\data\page\PageNode;
use wcf\data\page\PageNodeTree;
use wcf\system\condition\AbstractMultiSelectCondition;
use wcf\system\condition\AbstractSingleFieldCondition;
use wcf\system\condition\IContentCondition;
use wcf\system\exception\UserInputException;
use wcf\system\request\RequestHandler;

/**
 * Condition implementation for selecting multiple pages.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition\Page
 * @since	3.0
 */
class MultiPageCondition extends AbstractMultiSelectCondition implements IContentCondition {
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
		
		$fieldElement = '<ul class="scrollableCheckboxList">';
		/** @var PageNode $pageNode */
		foreach ($pageNodes as $pageNode) {
			$fieldElement .= '<li';
			if ($pageNode->getDepth() > 1) {
				$fieldElement .= ' style="padding-left: '.($pageNode->getDepth()*20-20).'px"';
			}
			$fieldElement .= '><label><input type="checkbox" name="'.$this->fieldName.'[]" value="'.$pageNode->pageID.'"';
			if (in_array($pageNode->pageID, $this->fieldValue)) {
				$fieldElement .= ' checked';
			}
			$fieldElement .= '> '.$pageNode->name.'</label></li>';
		}
		$fieldElement .= "</ul>";
		
		return $fieldElement;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHTML() {
		return AbstractSingleFieldCondition::getHTML();
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
