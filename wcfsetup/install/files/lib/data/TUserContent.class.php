<?php
namespace wcf\data;

/**
 * Default implementation of the (non-inherited) methods of the IUserContent interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 */
trait TUserContent {
	/**
	 * @see	IUserContent::getTime()
	 */
	public function getTime() {
		return $this->time;
	}
	
	/**
	 * @see	IUserContent::getUserID()
	 */
	public function getUserID() {
		return $this->userID;
	}
	
	/**
	 * @see	IUserContent::getUsername()
	 */
	public function getUsername() {
		return $this->username;
	}
}
