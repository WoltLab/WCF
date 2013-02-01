<?php
namespace wcf\acp\action;
use wcf\action\AbstractAction;
use wcf\system\cache\CacheHandler;
use wcf\system\exception\SystemException;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\style\StyleHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\HeaderUtil;

/**
 * Clears the cache.
 * 
 * @author	Tim Düsterhus
 * @copyright	2012 Tim Düsterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category	Community Framework
 */
class CacheClearAction extends AbstractAction {
	/**
	 * @see	wcf\action\AbstractAction::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.canViewLog');
	
	/**
	 * @see	wcf\action\IAction::execute()
	 */
	public function execute() {
		parent::execute();
		
		// reset stylesheets
		StyleHandler::resetStylesheets();
		
		// delete language cache and compiled templates as well
		LanguageFactory::getInstance()->deleteLanguageCache();
		
		// get package dirs
		$sql = "SELECT	packageDir
			FROM	wcf".WCF_N."_package
			WHERE	isApplication = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(1));
		while ($row = $statement->fetchArray()) {
			$packageDir = FileUtil::getRealPath(WCF_DIR . $row['packageDir']);
			try {
				CacheHandler::getInstance()->clear($packageDir.'cache', '*.php');
			}
			catch (SystemException $e) { }
		}
		
		$this->executed();
		HeaderUtil::redirect(LinkHandler::getInstance()->getLink('CacheList'));
		exit;
	}
}
