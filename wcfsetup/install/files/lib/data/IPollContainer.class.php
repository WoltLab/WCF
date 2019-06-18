<?php
namespace wcf\data;

/**
 * Every database object that supports polls has to implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 * @since	5.2
 */
interface IPollContainer extends IIDObject, IPollObject {
	/**
	 * Returns the id of the poll that belongs to this object or `null` if there is no such poll.
	 *
	 * @return	null|integer
	 */
	public function getPollID();
}
