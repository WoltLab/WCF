<?php
namespace wcf\data;

/**
 * This interface provides a method to access the title of a database object.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 */
interface ITitledDatabaseObject {
	/**
	 * Returns the title of this database object.
	 * 
	 * @return	string
	 */
	public function getTitle();
}
