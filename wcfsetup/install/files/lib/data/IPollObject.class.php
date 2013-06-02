<?php
namespace wcf\data;

/**
 * Default interface for DatabaseObjects with poll support.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.poll
 * @subpackage	data
 * @category	Community Framework
 */
interface IPollObject {
	/**
	 * Returns true if user can vote in polls.
	 * 
	 * @return	boolean
	 */
	public function canVote();
}
