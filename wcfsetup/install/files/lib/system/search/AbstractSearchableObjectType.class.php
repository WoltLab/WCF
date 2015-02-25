<?php
namespace wcf\system\search;
use wcf\data\object\type\AbstractObjectTypeProcessor;
use wcf\form\IForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * This class provides default implementations for the ISearchableObjectType interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search
 * @category	Community Framework
 */
abstract class AbstractSearchableObjectType extends AbstractObjectTypeProcessor implements ISearchableObjectType {
	/**
	 * active main menu item
	 * @var	string
	 */
	protected $activeMenuItem = '';
	
	/**
	 * @see	\wcf\system\search\ISearchableObjectType::show()
	 */
	public function show(IForm $form = null) {}
	
	/**
	 * @see	\wcf\system\search\ISearchableObjectType::getApplication()
	 */
	public function getApplication() {
		$classParts = explode('\\', get_called_class());
		return $classParts[0];
	}
	
	/**
	 * @see	\wcf\system\search\ISearchableObjectType::getConditions()
	 */
	public function getConditions(IForm $form = null) {
		return null;
	}
	
	/**
	 * @see	\wcf\system\search\ISearchableObjectType::getJoins()
	 */
	public function getJoins() {
		return '';
	}
	
	/**
	 * @see	\wcf\system\search\ISearchableObjectType::getSubjectFieldName()
	 */
	public function getSubjectFieldName() {
		return $this->getTableName().'.subject';
	}
	
	/**
	 * @see	\wcf\system\search\ISearchableObjectType::getUsernameFieldName()
	 */
	public function getUsernameFieldName() {
		return $this->getTableName().'.username';
	}
	
	/**
	 * @see	\wcf\system\search\ISearchableObjectType::getTimeFieldName()
	 */
	public function getTimeFieldName() {
		return $this->getTableName().'.time';
	}
	
	/**
	 * @see	\wcf\system\search\ISearchableObjectType::getAdditionalData()
	 */
	public function getAdditionalData() {
		return null;
	}
	
	/**
	 * @see	\wcf\system\search\ISearchableObjectType::isAccessible()
	 */
	public function isAccessible() {
		return true;
	}
	
	/**
	 * @see	\wcf\system\search\ISearchableObjectType::getFormTemplateName()
	 */
	public function getFormTemplateName() {
		return '';
	}
	
	/**
	 * @see	\wcf\system\search\ISearchableObjectType::getOuterSQLQuery()
	 */
	public function getOuterSQLQuery($q, PreparedStatementConditionBuilder &$searchIndexConditions = null, PreparedStatementConditionBuilder &$additionalConditions = null) {
		return '';
	}
	
	/**
	 * @see	\wcf\system\search\ISearchableObjectType::getActiveMenuItem()
	 */
	public function getActiveMenuItem() {
		return $this->activeMenuItem;
	}
}
