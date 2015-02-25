<?php
namespace wcf\acp\action;
use wcf\action\AbstractAction;
use wcf\system\cache\CacheHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\style\StyleHandler;
use wcf\util\HeaderUtil;

/**
 * Clears the cache.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category	Community Framework
 */
class CacheClearAction extends AbstractAction {
	/**
	 * @see	\wcf\action\AbstractAction::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.canViewLog');
	
	/**
	 * @see	\wcf\action\IAction::execute()
	 */
	public function execute() {
		parent::execute();
		
		// reset stylesheets
		StyleHandler::resetStylesheets();
		
		// delete language cache and compiled templates as well
		LanguageFactory::getInstance()->deleteLanguageCache();
		
		// get package dirs
		CacheHandler::getInstance()->flushAll();
		
		$this->executed();
		
		if (!isset($_POST['noRedirect'])) {
			HeaderUtil::redirect(LinkHandler::getInstance()->getLink('CacheList'));
		}
		
		exit;
	}
}
