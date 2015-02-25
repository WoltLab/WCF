<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\page\PageManager;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Condition implementation for selecting multiple page controllers.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
class MultiPageControllerCondition extends AbstractMultiSelectCondition implements IContentCondition {
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
	 * @see	\wcf\system\condition\AbstractSelectCondition::getOptionCode()
	 */
	protected function getOptionCode($value, $label) {
		return '<option value="'.$value.'" data-object-type="'.ObjectTypeCache::getInstance()->getObjectType($value)->objectType.'"'.(in_array($value, $this->fieldValue) ? ' selected="selected"' : '').'>'.WCF::getLanguage()->get($label).'</option>';
	}
	
	/**
	 * @see	\wcf\system\condition\AbstractSelectCondition::getOptions()
	 */
	protected function getOptions() {
		return PageManager::getInstance()->getSelection();
	}
	
	/**
	 * @see	\wcf\system\condition\IContentCondition::showContent()
	 */
	public function showContent(Condition $condition) {
		$requestClassName = RequestHandler::getInstance()->getActiveRequest()->getClassName();
		$requestClassName = ltrim($requestClassName, '\\'); // remove leading backslash
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
