<?php
namespace wcf\data;

/**
 * Every titled object has to implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 */
interface ITitledObject {
	/**
	 * Returns the title of the object.
	 * 
	 * @return	string
	 */
	public function getTitle();
}
