<?php
namespace wcf\system\search;
use wcf\data\object\type\AbstractObjectTypeProcessor;
use wcf\form\IForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * This class provides default implementations for the ISearchableObjectType interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Search
 */
abstract class AbstractSearchableObjectType extends AbstractObjectTypeProcessor implements ISearchableObjectType {
	/**
	 * @inheritDoc
	 */
	public function show(IForm $form = null) {}
	
	/**
	 * @inheritDoc
	 */
	public function getApplication() {
		$classParts = explode('\\', get_called_class());
		return $classParts[0];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getConditions(IForm $form = null) {
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getJoins() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSubjectFieldName() {
		return $this->getTableName().'.subject';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getUsernameFieldName() {
		return $this->getTableName().'.username';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTimeFieldName() {
		return $this->getTableName().'.time';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getAdditionalData() {
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function isAccessible() {
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFormTemplateName() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getOuterSQLQuery($q, PreparedStatementConditionBuilder &$searchIndexConditions = null, PreparedStatementConditionBuilder &$additionalConditions = null) {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function setLocation() {}
	
	/**
	 * @inheritDoc
	 */
	public function getActiveMenuItem() {
		return '';
	}
}
