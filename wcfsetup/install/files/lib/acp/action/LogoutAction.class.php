<?php
namespace wcf\acp\action;
use wcf\action\AbstractSecureAction;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Does the user logout in the admin control panel.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category	Community Framework
 */
class LogoutAction extends AbstractSecureAction {
	/**
	 * @see	\wcf\action\AbstractAction::$loginRequired
	 */
	public $loginRequired = true;
	
	/**
	 * @see	\wcf\action\IAction::execute()
	 */
	public function execute() {
		parent::execute();
		
		// do logout
		WCF::getSession()->delete();
		
		$this->executed();
		
		// forward to index page
		// warning: if doLogout() writes a cookie this is buggy in MS IIS
		HeaderUtil::redirect(LinkHandler::getInstance()->getLink('Login'));
		exit;
	}
}
