<?php
namespace wcf\system\condition;
use wcf\data\object\type\AbstractObjectTypeProcessor;

/**
 * Abstract implementation of a condition.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
abstract class AbstractCondition extends AbstractObjectTypeProcessor implements ICondition {
	/**
	 * @see	\wcf\system\condition\ICondition::reset()
	 */
	public function reset() {
		// does nothing
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::validate()
	 */
	public function validate() {
		// does nothing
	}
}
