<?php
namespace wcf\system\condition\user\trophy;
use wcf\data\trophy\category\TrophyCategoryCache;
use wcf\data\user\trophy\UserTrophyList;
use wcf\data\DatabaseObjectList;
use wcf\system\condition\AbstractMultiSelectCondition;
use wcf\system\condition\IObjectListCondition;

/**
 * Condition implementation for the excluded trophies.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition\User\Trophy
 * @since	3.1
 */
class UserTrophyExcludedTrophyCategoriesCondition extends AbstractMultiSelectCondition implements IObjectListCondition {
	/**
	 * @inheritDoc
	 */
	protected $description = 'wcf.global.multiSelect';
	
	/**
	 * @inheritDoc
	 */
	protected $fieldName = 'userTrophyExcludedTrophyCategories';
	
	/**
	 * @inheritDoc
	 */
	protected $label = 'wcf.user.trophy.condition.excludedTrophyCategories';
	
	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof UserTrophyList)) {
			throw new \InvalidArgumentException("Object list is no instance of '".UserTrophyList::class."', instance of '".get_class($objectList)."' given.");
		}
		
		$objectList->getConditionBuilder()->add('trophy.categoryID NOT IN (?)', [$conditionData[$this->fieldName]]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getOptions() {
		$categories = TrophyCategoryCache::getInstance()->getCategories();
		
		$options = [];
		foreach ($categories as $category) {
			$options[$category->categoryID] = $category->getTitle();
		}
		
		asort($options);
		
		return $options;
	}
}
