<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;

/**
 * Condition implementation for selecting multiple page controllers.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 * @deprecated	since 2.2
 */
class MultiPageControllerCondition extends AbstractMultiSelectCondition implements IContentCondition {
	/**
	 * @inheritDoc
	 */
	protected function getFieldElement() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getOptions() {
		return [];
	}
	
	/**
	 * @inheritDoc
	 */
	public function showContent(Condition $condition) {
		return false;
	}
}
