<?php
namespace wcf\system\notice;
use wcf\system\cache\builder\NoticeCacheBuilder;
use wcf\system\SingletonFactory;

/**
 * Handles notice-related matters.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.notice
 * @category	Community Framework
 */
class NoticeHandler extends SingletonFactory {
	/**
	 * list with all enabled notices
	 * @var	array<\wcf\data\notice\Notice>
	 */
	protected $notices = array();
	
	/**
	 * @see	\wcf\system\SingletonFacetory::init()
	 */
	protected function init() {
		$this->notices = NoticeCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * Returns the notices which are visible for the active user.
	 * 
	 * @return	array<\wcf\data\notice\Notice>
	 */
	public function getVisibleNotices() {
		$notices = array();
		foreach ($this->notices as $notice) {
			if ($notice->isDismissed()) continue;
			
			$conditions = $notice->getConditions();
			foreach ($conditions as $condition) {
				if (!$condition->getObjectType()->getProcessor()->showContent($condition)) {
					continue 2;
				}
			}
			
			$notices[$notice->noticeID] = $notice;
		}
		
		return $notices;
	}
}
