<?php
namespace wcf\data;

/**
 * Default interface for objects supporting visit tracking.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	data
 * @category	Community Framework
 */
interface IVisitableObjectAction {
	/**
	 * Marks objects as read.
	 */
	public function markAsRead();
	
	/**
	 * Validates parameters to mark objects as read.
	 */
	public function validateMarkAsRead();
}
