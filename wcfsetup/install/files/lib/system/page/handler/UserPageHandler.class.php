<?php
namespace wcf\system\page\handler;
use wcf\data\page\Page;
use wcf\data\user\online\UserOnline;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\WCF;

/**
 * Menu page handler for the user profile page.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.page.handler
 * @category	Community Framework
 * @since	2.2
 */
class UserPageHandler extends AbstractMenuPageHandler implements IOnlineLocationPageHandler {
	use TOnlineLocationPageHandler;
	
	/**
	 * @inheritDoc
	 */
	public function getOnlineLocation(Page $page, UserOnline $user) {
		if ($user->objectID === null) {
			return '';
		}
		
		$visitedUser = UserRuntimeCache::getInstance()->getObject($user->objectID);
		if ($visitedUser === null) {
			return '';
		}
		
		return WCF::getLanguage()->getDynamicVariable('wcf.page.onlineLocation.'.$page->identifier, ['user' => $visitedUser]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function prepareOnlineLocation(Page $page, UserOnline $user) {
		if ($user->objectID !== null) {
			UserRuntimeCache::getInstance()->cacheObjectID($user->objectID);
		}
	}
}
