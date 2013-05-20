<?php
namespace wcf\system\search;
use wcf\data\object\type\AbstractObjectTypeProcessor;
use wcf\form\IForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * This class provides default implementations for the ISearchableObjectType interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.search
 * @subpackage	system.search
 * @category	Community Framework
 */
abstract class AbstractSearchableObjectType extends AbstractObjectTypeProcessor implements ISearchableObjectType {
	/**
	 * @see	wcf\system\search\ISearchableObjectType::show()
	 */
	public function show(IForm $form = null) {}
	
	/**
	 * @see	wcf\system\search\ISearchableObjectType::getApplication()
	 */
	public function getApplication() {
		return 'wcf';
	}
	
	/**
	 * @see	wcf\system\search\ISearchableObjectType::getConditions()
	 */
	public function getConditions(IForm $form = null) {
		return null;
	}
	
	/**
	 * @see	wcf\system\search\ISearchableObjectType::getJoins()
	 */
	public function getJoins() {
		return '';
	}
	
	/**
	 * @see	wcf\system\search\ISearchableObjectType::getSubjectFieldName()
	 */
	public function getSubjectFieldName() {
		return $this->getTableName().'.subject';
	}
	
	/**
	 * @see	wcf\system\search\ISearchableObjectType::getUsernameFieldName()
	 */
	public function getUsernameFieldName() {
		return $this->getTableName().'.username';
	}
	
	/**
	 * @see	wcf\system\search\ISearchableObjectType::getTimeFieldName()
	 */
	public function getTimeFieldName() {
		return $this->getTableName().'.time';
	}
	
	/**
	 * @see	wcf\system\search\ISearchableObjectType::getAdditionalData()
	 */
	public function getAdditionalData() {
		return null;
	}
	
	/**
	 * @see	wcf\system\search\ISearchableObjectType::isAccessible()
	 */
	public function isAccessible() {
		return true;
	}
	
	/**
	 * @see	wcf\system\search\ISearchableObjectType::getFormTemplateName()
	 */
	public function getFormTemplateName() {
		return '';
	}
	
	/**
	 * @see	wcf\system\search\ISearchableObjectType::getSpecialSQLQuery()
	 */
	public function getSpecialSQLQuery(PreparedStatementConditionBuilder $fulltextCondition = null, PreparedStatementConditionBuilder $searchIndexConditions = null, PreparedStatementConditionBuilder $additionalConditions = null, $orderBy = 'time DESC') {
		return '';
	}
}
