<?php
namespace wcf\data;

/**
 * Default interface for user generated content.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 */
interface IUserContent extends ITitledLinkObject {
	/**
	 * Returns message creation timestamp.
	 * 
	 * @return	integer
	 */
	public function getTime();
	
	/**
	 * Returns author's user id.
	 * 
	 * @return	integer
	 */
	public function getUserID();
	
	/**
	 * Returns author's username.
	 * 
	 * @return	string
	 */
	public function getUsername();
}
