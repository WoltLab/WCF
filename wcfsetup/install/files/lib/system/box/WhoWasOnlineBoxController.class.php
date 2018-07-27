<?php
namespace wcf\system\box;
use wcf\data\user\online\UsersOnlineList;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObject;
use wcf\system\cache\builder\WhoWasOnlineCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\event\EventHandler;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Box controller for a list of registered users that visited the website in last 24 hours.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Box
 * @since	3.0
 */
class WhoWasOnlineBoxController extends AbstractDatabaseObjectListBoxController {
	/**
	 * @inheritDoc
	 */
	protected static $supportedPositions = ['footerBoxes', 'sidebarLeft', 'sidebarRight'];
	
	/**
	 * @inheritDoc
	 */
	protected $sortFieldLanguageItemPrefix = 'wcf.user.sortField';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = [
		'username',
		'lastActivityTime'
	];
	
	/**
	 * users loaded from cache
	 * @var	UserProfile[]
	 */
	public $users = [];
	
	/**
	 * @inheritDoc
	 */
	protected function getObjectList() {
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getTemplate() {
		return WCF::getTPL()->fetch('boxWhoWasOnline', 'wcf', [
			'whoWasOnlineList' => $this->users,
			'boxPosition' => $this->box->position,
			'whoWasOnlineTimeFormat' => DateUtil::TIME_FORMAT
		], true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasContent() {
		if (!MODULE_USERS_ONLINE || !WCF::getSession()->getPermission('user.profile.canViewUsersOnlineList')) {
			return false;
		}
		
		parent::hasContent();
		
		return count($this->users) > 0;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		$this->readObjects();
		
		$this->content = $this->getTemplate();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function readObjects() {
		EventHandler::getInstance()->fireAction($this, 'readObjects');
		
		$userIDs = WhoWasOnlineCacheBuilder::getInstance()->getData();
		
		if (!empty($userIDs)) {
			if (WCF::getUser()->userID && !in_array(WCF::getUser()->userID, $userIDs)) {
				// current user is missing in cache -> reset cache
				WhoWasOnlineCacheBuilder::getInstance()->reset();
			}
			
			$this->users = array_filter(UserProfileRuntimeCache::getInstance()->getObjects($userIDs), function($user) {
				return $user !== null;
			});
			foreach ($this->users as $key => $user) {
				// remove invisible users
				if (!UsersOnlineList::isVisible($user->userID, $user->canViewOnlineStatus)) {
					unset($this->users[$key]);
				}
			}
			
			// sort users
			if (!empty($this->users)) {
				DatabaseObject::sort($this->users, $this->sortField, $this->sortOrder);
			}
		}
	}
}
