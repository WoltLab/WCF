<?php
namespace wcf\system\exception;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;

/**
 * NamedUserException shows a (well) styled page with the given error message.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Exception
 */
class NamedUserException extends UserException {
	/**
	 * Shows a styled page with the given error message.
	 */
	public function show() {
		SessionHandler::getInstance()->disableTracking();
		
		WCF::getTPL()->assign([
			'name' => get_class($this),
			'file' => $this->getFile(),
			'line' => $this->getLine(),
			'message' => $this->getMessage(),
			'stacktrace' => $this->getTraceAsString(),
			'templateName' => 'userException',
			'templateNameApplication' => 'wcf'
		]);
		WCF::getTPL()->display('userException');
	}
}
