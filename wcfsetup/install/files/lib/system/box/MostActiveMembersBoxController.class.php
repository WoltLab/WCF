<?php
namespace wcf\system\box;
use wcf\data\DatabaseObject;
use wcf\system\cache\builder\MostActiveMembersCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows a list of the most active members.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.box
 * @category	Community Framework
 */
class MostActiveMembersBoxController extends AbstractBoxController {
	/**
	 * @inheritDoc
	 */
	protected $supportedPositions = ['sidebarLeft', 'sidebarRight'];
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return WCF::getLanguage()->get('wcf.page.mostActiveMembers'); // @todo
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
			return LinkHandler::getInstance()->getLink('MembersList', [], 'sortField=activityPoints&sortOrder=DESC');
		}
		
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		// get ids
		$mostActiveMemberIDs = MostActiveMembersCacheBuilder::getInstance()->getData();
		if (!empty($mostActiveMemberIDs)) {
			UserProfileRuntimeCache::getInstance()->cacheObjectIDs($mostActiveMemberIDs);
			
			// get users
			$mostActiveMembers = UserProfileRuntimeCache::getInstance()->getObjects($mostActiveMemberIDs);
			DatabaseObject::sort($mostActiveMembers, 'activityPoints', 'DESC');
			
			WCF::getTPL()->assign([
				'mostActiveMembers' => $mostActiveMembers
			]);
			$this->content = WCF::getTPL()->fetch('boxMostActiveMembers');
		}
	}
}
