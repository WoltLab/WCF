<?php
namespace wcf\data;

/**
 * Default interface for user generated content.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 */
interface IUserContent extends ILinkableObject, ITitledObject {
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
