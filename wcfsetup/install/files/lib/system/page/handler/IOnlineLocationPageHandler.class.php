<?php
namespace wcf\system\page\handler;
use wcf\data\page\Page;
use wcf\data\user\online\UserOnline;

/**
 * Interface for pages supporting online location.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Page\Handler
 * @since	3.0
 */
interface IOnlineLocationPageHandler {
	/**
	 * Returns the textual description if a user is currently online viewing this page.
	 *
	 * @param	Page		$page		visited page
	 * @param	UserOnline	$user		user online object with request data
	 * @return	string
	 */
	public function getOnlineLocation(Page $page, UserOnline $user);
	
	/**
	 * Prepares fetching all necessary data for the textual description if a user is currently online
	 * viewing this page.
	 *
	 * @param	Page		$page		visited page
	 * @param	UserOnline	$user		user online object with request data
	 */
	public function prepareOnlineLocation(Page $page, UserOnline $user);
}
