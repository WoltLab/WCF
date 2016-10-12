<?php
namespace wcf\system\condition;
use wcf\data\object\type\AbstractObjectTypeProcessor;

/**
 * Abstract implementation of a condition.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition
 */
abstract class AbstractCondition extends AbstractObjectTypeProcessor implements ICondition {
	/**
	 * @inheritDoc
	 */
	public function reset() {
		// does nothing
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		// does nothing
	}
}
