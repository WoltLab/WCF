<?php
namespace wcf\data;

/**
 * Every database object representing a file should implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 * @since	2.2
 */
interface IFile extends IStorableObject {
	/**
	 * Returns the physical location of the file.
	 * 
	 * @return	string
	 */
	public function getLocation();
}
