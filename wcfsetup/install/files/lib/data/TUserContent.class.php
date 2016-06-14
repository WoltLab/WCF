<?php
namespace wcf\data;

/**
 * Default implementation of the (non-inherited) methods of the IUserContent interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
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
