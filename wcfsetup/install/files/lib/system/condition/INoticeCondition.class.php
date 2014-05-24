<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;

/**
 * Every implementation for notice conditions needs to implements this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
interface INoticeCondition extends ICondition {
	/**
	 * Returns true if a notice with the given condition will be shown.
	 * 
	 * All necessary data to check the condition needs to be globally available
	 * like the active user object via WCF::getUser().
	 * 
	 * @param	\wcf\data\condition\Condition	$condition
	 * @return	boolean
	 */
	public function showNotice(Condition $condition);
}
