<?php
namespace wcf\system\box;
use wcf\data\DatabaseObject;
use wcf\system\cache\builder\NewestMembersCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows a list of the newest members.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.box
 * @category	Community Framework
 */
class NewestMembersBoxController extends AbstractBoxController {
	/**
	 * @inheritDoc
	 */
	protected $supportedPositions = ['sidebarLeft', 'sidebarRight'];
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return WCF::getLanguage()->get('wcf.page.newestMembers'); // @todo
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasImage() {
		if (MODULE_MEMBERS_LIST) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		if (MODULE_MEMBERS_LIST) {
			return LinkHandler::getInstance()->getLink('MembersList', [], 'sortField=registrationDate&sortOrder=DESC');
		}
		
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		// get ids
		$newestMemberIDs = NewestMembersCacheBuilder::getInstance()->getData();
		if (!empty($newestMemberIDs)) {
			UserProfileRuntimeCache::getInstance()->cacheObjectIDs($newestMemberIDs);
			
			// get users
			$newestMembers = UserProfileRuntimeCache::getInstance()->getObjects($newestMemberIDs);
			DatabaseObject::sort($newestMembers, 'registrationDate', 'DESC');
			
			WCF::getTPL()->assign([
				'newestMembers' => $newestMembers
			]);
			$this->content = WCF::getTPL()->fetch('boxNewestMembers');
		}
	}
}
