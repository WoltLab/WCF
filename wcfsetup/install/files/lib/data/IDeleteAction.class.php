<?php
namespace wcf\data;

/**
 * Every database object action whose objects can be deleted (via AJAX) has to
 * implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 */
interface IDeleteAction {
	/**
	 * Deletes the relevant objects and returns the number of deleted objects.
	 * 
	 * @return	integer
	 */
	public function delete();
	
	/**
	 * Validates the "delete" action.
	 */
	public function validateDelete();
}
