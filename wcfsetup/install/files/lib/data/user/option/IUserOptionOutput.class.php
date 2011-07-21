<?php
namespace wcf\data\user\option;
use wcf\data\User;

/**
 * Any user option output class should implement this interface.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.option
 * @category 	Community Framework
 */
interface IUserOptionOutput {
	/**
	 * Returns a short version of the html code for the output of the given user option.
	 * 
	 * @param	User		$user
	 * @param	array		$optionData
	 * @param	string		$value
	 * @return	string
	 */
	public function getShortOutput(User $user, $optionData, $value);
	
	/**
	 * Returns a medium version of the html code for the output of the given user option.
	 * 
	 * @param	User		$user
	 * @param	array		$optionData
	 * @param	string		$value
	 * @return	string
	 */
	public function getMediumOutput(User $user, $optionData, $value);
	
	/**
	 * Returns the html code for the output of the given user option.
	 * 
	 * @param	User		$user
	 * @param	array		$optionData
	 * @param	string		$value
	 * @return	string
	 */
	public function getOutput(User $user, $optionData, $value);
}
