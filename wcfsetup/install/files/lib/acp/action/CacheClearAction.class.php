<?php
namespace wcf\acp\action;
use wcf\action\AbstractAction;
use wcf\system\cache\CacheHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Clears the cache.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class CacheClearAction extends AbstractAction {
	/**
	 * @see	wcf\action\AbstractAction::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.canViewLog');
	
	/**
	 * @see	wcf\action\Action::execute()
	 */
	public function execute() {
		parent::execute();

		// clear cache
		CacheHandler::getInstance()->getCacheSource()->flush();
		$this->executed();

		// set cacheCleared
		WCF::getSession()->register('cacheCleared', true);

		// foward to cache list page
		HeaderUtil::redirect('index.php?page=CacheList&package='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
