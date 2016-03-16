<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\User;
use wcf\system\WCF;

/**
 * Condition implementation for comparing a user-bound timestamp with a fixed time
 * interval.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 * @since	2.2
 */
class UserTimestampPropertyCondition extends AbstractTimestampCondition implements IContentCondition, IUserCondition {
	use TObjectListUserCondition;
	use TObjectUserCondition;
	
	/**
	 * @see	\wcf\system\condition\AbstractTimestampCondition::$className
	 */
	protected $className = User::class;
	
	/**
	 * @see	\wcf\system\condition\AbstractTimestampCondition::getLanguageItemPrefix()
	 */
	protected function getLanguageItemPrefix() {
		return 'wcf.user.condition';
	}
	
	/**
	 * @see	\wcf\system\condition\AbstractTimestampCondition::getPropertyName()
	 */
	protected function getPropertyName() {
		return $this->getDecoratedObject()->propertyname;
	}
	
	/**
	 * @see	\wcf\system\condition\IContentCondition::showContent()
	 */
	public function showContent(Condition $condition) {
		if (!WCF::getUser()->userID) return false;
		
		return $this->checkUser($condition, WCF::getUser());
	}
}
