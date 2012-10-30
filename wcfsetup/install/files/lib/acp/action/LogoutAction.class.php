<?php
namespace wcf\acp\action;
use wcf\action\AbstractSecureAction;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Does the user logout in the admin control panel.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category	Community Framework
 */
class LogoutAction extends AbstractSecureAction {
	/**
	 * @see wcf\action\IAction::execute()
	 */
	public function execute() {
		parent::execute();
		
		// validate
		if (!WCF::getUser()->userID) {
			throw new IllegalLinkException();
		}
		
		// do logout
		WCF::getSession()->delete();
		
		$this->executed();
		
		// forward to index page
		// warning: if doLogout() writes a cookie this is buggy in MS IIS
		$application = ApplicationHandler::getInstance()->getActiveApplication();
		$path = $application->getPageURL() . 'acp/index.php' . SID_ARG_1ST;
		HeaderUtil::redirect($path);
		exit;
	}
}
