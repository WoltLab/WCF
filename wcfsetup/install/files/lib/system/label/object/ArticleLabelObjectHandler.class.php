<?php
namespace wcf\system\label\object;
use wcf\system\cache\builder\ArticleCategoryLabelCacheBuilder;
use wcf\system\label\LabelHandler;

/**
 * Label handler for articles.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Label\Object
 * @since       3.1
 */
class ArticleLabelObjectHandler extends AbstractLabelObjectHandler {
	/**
	 * @inheritDoc
	 */
	protected $objectType = 'com.woltlab.wcf.article';
	
	/**
	 * Sets the label groups available for the categories with the given ids.
	 * 
	 * @param	integer[]		$categoryIDs
	 */
	public function setCategoryIDs($categoryIDs) {
		$labelGroupsToCategories = ArticleCategoryLabelCacheBuilder::getInstance()->getData();
		
		$groupIDs = [];
		foreach ($labelGroupsToCategories as $categoryID => $__groupIDs) {
			if (in_array($categoryID, $categoryIDs)) {
				$groupIDs = array_merge($groupIDs, $__groupIDs);
			}
		}
		
		$this->labelGroups = [];
		if (!empty($groupIDs)) {
			$this->labelGroups = LabelHandler::getInstance()->getLabelGroups(array_unique($groupIDs));
		}
	}
}
