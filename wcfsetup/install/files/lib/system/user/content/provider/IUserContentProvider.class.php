<?php
namespace wcf\system\user\content\provider;
use wcf\data\DatabaseObjectList;
use wcf\data\user\User;

/**
 * User Content Provider interface.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Content\Provider
 * @since	5.2
 */
interface IUserContentProvider {
	/**
	 * Returns a DatabaseObjectList with all user content objects. 
	 * 
	 * @param       User                    $user
	 * @return      DatabaseObjectList
	 */
	public function getContentListForUser(User $user);
	
	/**
	 * Delete the content for the given object ids.
	 * 
	 * @param       int[]   $objectIDs
	 */
	public function deleteContent(array $objectIDs);
}
