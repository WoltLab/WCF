<?php
namespace wcf\system\cache\builder;
use wcf\data\notice\NoticeList;

/**
 * Caches the enabled notices.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class NoticeCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	protected function rebuild(array $parameters) {
		$noticeList = new NoticeList();
		$noticeList->getConditionBuilder()->add('isDisabled = ?', array(0));
		$noticeList->sqlOrderBy = 'showOrder ASC';
		$noticeList->readObjects();
		
		return $noticeList->getObjects();
	}
}
