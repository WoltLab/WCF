<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\page\PageManager;
use wcf\system\request\RequestHandler;

/**
 * Condition implementation for selecting multiple page controllers.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
class MultiPageControllerCondition extends AbstractMultiSelectCondition implements INoticeCondition {
	/**
	 * @see	\wcf\system\condition\AbstractSingleFieldCondition::$label
	 */
	protected $description = 'wcf.global.multiSelect';
	
	/**
	 * @see	\wcf\system\condition\AbstractSelectCondition::$fieldName
	 */
	protected $fieldName = 'pageControllers';
	
	/**
	 * @see	\wcf\system\condition\AbstractSingleFieldCondition::$label
	 */
	protected $label = 'wcf.page.requestedPage';
	
	/**
	 * @see	\wcf\system\condition\AbstractSelectCondition::getOptions()
	 */
	protected function getOptions() {
		return PageManager::getInstance()->getSelection();
	}
	
	/**
	 * @see	\wcf\system\condition\INoticeCondition::showNotice()
	 */
	public function showNotice(Condition $condition) {
		$requestClassName = RequestHandler::getInstance()->getActiveRequest()->getClassName();
		$pageControllers = $condition->pageControllers;
		foreach ($pageControllers as $objectTypeID) {
			$objectType = ObjectTypeCache::getInstance()->getObjectType($objectTypeID);
			if ($objectType === null) return false;
			
			if ($requestClassName == $objectType->className) {
				return true;
			}
		}
		
		return false;
	}
}
