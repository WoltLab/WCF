<?php
namespace wcf\system\exception;
use wcf\system\box\BoxHandler;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;
use wcf\system\WCFACP;

/**
 * NamedUserException shows a (well) styled page with the given error message.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Exception
 */
class NamedUserException extends UserException {
	/**
	 * Shows a styled page with the given error message.
	 */
	public function show() {
		if (!class_exists(WCFACP::class, false)) {
			BoxHandler::disablePageLayout();
		}
		SessionHandler::getInstance()->disableTracking();
		
		$name = get_class($this);
		$exceptionClassName = mb_substr($name, mb_strrpos($name, '\\') + 1);
		
		WCF::getTPL()->assign([
			'name' => $name,
			'file' => $this->getFile(),
			'line' => $this->getLine(),
			'message' => $this->getMessage(),
			'stacktrace' => $this->getTraceAsString(),
			'templateName' => 'userException',
			'templateNameApplication' => 'wcf',
			'exceptionClassName' => $exceptionClassName
		]);
		WCF::getTPL()->display('userException');
	}
}
