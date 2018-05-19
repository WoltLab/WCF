<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\data\page\PageNodeTree;
use wcf\system\cache\builder\PageCacheBuilder;
use wcf\system\exception\UserInputException;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Page option type implementation for a page select list.
 * 
 * @author	Christopher Walz
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PagesOptionType extends AbstractOptionType {
	/**
	 * @inheritDoc
	 */
	public function getFormElement(Option $option, $value) {
		// get selected pages
		$selectedPages = explode(',', $value);
		
		// get the page node tree
		$pages = (new PageNodeTree())->getNodeList();

		// generate html
		$html = '<select id="'.StringUtil::encodeHTML($option->optionName).'" name="values['.StringUtil::encodeHTML($option->optionName).'][]" multiple size="10">';
		
		foreach ($pages as $page) {
			$pagePadding = '';
			if ($page->getDepth() > 1) {
				$pagePadding = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $page->getDepth() -1);
			}
			
			$html .= '<option value="'.$page->pageID.'"'.(in_array($page->pageID, $selectedPages) ? ' selected' : '').'> '.	$pagePadding . $page->name.'</option>';
		}
		
		$html .= '</select>';
		
		return $html;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate(Option $option, $newValue) {
		// get all pages
		$data = PageCacheBuilder::getInstance()->getData();
		$pages = $data['pages'];
		
		// get new value
		if (!is_array($newValue)) $newValue = [];
		$selectedPages = ArrayUtil::toIntegerArray($newValue);
		
		// check pages
		foreach ($selectedPages as $pageID) {
			if (!isset($pages[$pageID])) {
				throw new UserInputException($option->optionName, 'validationFailed');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData(Option $option, $newValue) {
		if (!is_array($newValue)) $newValue = [];
		$newValue = ArrayUtil::toIntegerArray($newValue);
		sort($newValue, SORT_NUMERIC);
		return implode(',', $newValue);
	}
	
	/**
	 * @inheritDoc
	 */
	public function compare($value1, $value2) {
		$value1 = $value1 ? explode(',', $value1) : [];
		$value2 = $value2 ? explode(',', $value2) : [];
		
		// check if value1 contains more elements than value2
		$diff = array_diff($value1, $value2);
		if (!empty($diff)) {
			return 1;
		}
		
		// check if value1 contains less elements than value2
		$diff = array_diff($value2, $value1);
		if (!empty($diff)) {
			return -1;
		}
		
		// both lists are equal
		return 0;
	}
}
