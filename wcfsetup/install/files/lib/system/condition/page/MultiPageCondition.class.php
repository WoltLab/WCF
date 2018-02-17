<?php
namespace wcf\system\condition\page;
use wcf\data\condition\Condition;
use wcf\data\page\PageCache;
use wcf\data\page\PageNodeTree;
use wcf\system\condition\AbstractMultiSelectCondition;
use wcf\system\condition\AbstractSingleFieldCondition;
use wcf\system\condition\IContentCondition;
use wcf\system\exception\UserInputException;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Condition implementation for selecting multiple pages.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
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
		return WCF::getTPL()->fetch('scrollablePageCheckboxList', 'wcf', [
			'pageCheckboxID' => $this->fieldName,
			'pageCheckboxListContainerID' => $this->fieldName . 'Container',
			'pageIDs' => $this->fieldValue,
			'pageNodeList' => (new PageNodeTree())->getNodeList()
		]);
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
		$activeRequest = RequestHandler::getInstance()->getActiveRequest();
		if ($activeRequest !== null) {
			$pageID = $activeRequest->getPageID();
			if ($pageID && $condition->pageIDs && is_array($condition->pageIDs)) {
				return in_array($pageID, $condition->pageIDs);
			}
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
