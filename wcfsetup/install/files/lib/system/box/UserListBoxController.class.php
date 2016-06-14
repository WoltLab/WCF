<?php
namespace wcf\system\box;
use wcf\data\user\UserProfileList;
use wcf\data\DatabaseObject;
use wcf\system\cache\builder\MostActiveMembersCacheBuilder;
use wcf\system\cache\builder\MostLikedMembersCacheBuilder;
use wcf\system\cache\builder\NewestMembersCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\event\EventHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Box controller for a list of users.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Box
 * @since	3.0
 */
class UserListBoxController extends AbstractDatabaseObjectListBoxController {
	/**
	 * maps special sort fields to cache builders
	 * @var	string[]
	 */
	public $cacheBuilders = [
		'activityPoints' => MostActiveMembersCacheBuilder::class,
		'likesReceived' => MostLikedMembersCacheBuilder::class,
		'registrationDate' => NewestMembersCacheBuilder::class
	];
	
	/**
	 * @inheritDoc
	 */
	protected $sortFieldLanguageItemPrefix = 'wcf.user';
	
	/**
	 * ids of the shown users loaded from cache
	 * @var	integer[]|null
	 */
	public $userIDs;
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = [
		'username',
		'activityPoints',
		'registrationDate'
	];
	
	/**
	 * @inheritDoc
	 */
	public function __construct() {
		if (!empty($this->validSortFields) && MODULE_LIKE) {
			$this->validSortFields[] = 'likesReceived';
		}
		
		parent::__construct();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		if (MODULE_MEMBERS_LIST) {
			$parameters = '';
			if ($this->box->sortField) {
				$parameters = 'sortField='.$this->box->sortField.'&sortOrder='.$this->box->sortOrder;
			}
			
			return LinkHandler::getInstance()->getLink('MembersList', [], $parameters);
		}
		
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getObjectList() {
		// use specialized cache builders
		if ($this->box->sortOrder && $this->box->sortField && isset($this->cacheBuilders[$this->box->sortField])) {
			$this->userIDs = call_user_func([$this->cacheBuilders[$this->box->sortField], 'getInstance'])->getData([
				'limit' => $this->box->limit,
				'sortOrder' => $this->sortOrder
			]);
		}
		
		if ($this->userIDs !== null) {
			UserProfileRuntimeCache::getInstance()->cacheObjectIDs($this->userIDs);
		}
		
		return new UserProfileList();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getTemplate() {
		$userProfiles = [];
		if ($this->userIDs !== null) {
			$userProfiles = UserProfileRuntimeCache::getInstance()->getObjects($this->userIDs);
			
			DatabaseObject::sort($userProfiles, $this->sortField, $this->sortOrder);
		}
		
		return WCF::getTPL()->fetch('boxUserList', 'wcf', [
			'boxUsers' => $this->userIDs !== null ? $userProfiles : $this->objectList->getObjects(),
			'boxSortField' => $this->box->sortField
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasContent() {
		$hasContent = parent::hasContent();
		
		if ($this->userIDs !== null) {
			return !empty($this->userIDs);
		}
		
		return $hasContent;
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasLink() {
		return MODULE_MEMBERS_LIST == 1;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		if ($this->userIDs === null) {
			parent::readObjects();
		}
		else {
			EventHandler::getInstance()->fireAction($this, 'readObjects');
		}
	}
}
