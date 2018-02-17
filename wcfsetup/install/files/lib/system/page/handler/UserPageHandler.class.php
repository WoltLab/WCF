<?php
namespace wcf\system\page\handler;
use wcf\system\cache\runtime\UserRuntimeCache;

/**
 * Menu page handler for the user profile page.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Page\Handler
 * @since	3.0
 */
class UserPageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler {
	use TUserLookupPageHandler;
	use TUserOnlineLocationPageHandler;
	
	/**
	 * @inheritDoc
	 */
	public function getLink($objectID) {
		return UserRuntimeCache::getInstance()->getObject($objectID)->getLink();
	}
}
