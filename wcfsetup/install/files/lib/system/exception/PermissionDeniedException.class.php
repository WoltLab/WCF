<?php
namespace wcf\system\exception;
use wcf\system\box\BoxHandler;
use wcf\system\notice\NoticeHandler;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;
use wcf\system\WCFACP;

/**
 * A PermissionDeniedException is thrown when a user has no permission to access
 * to a specific area.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Exception
 */
class PermissionDeniedException extends UserException {
	/**
	 * Creates a new PermissionDeniedException object.
	 * 
	 * @param	string|null	$message	custom error message
	 */
	public function __construct($message = null) {
		if ($message === null) $message = WCF::getLanguage()->getDynamicVariable('wcf.page.error.permissionDenied');
		parent::__construct($message);
	}
	
	/**
	 * Prints a permission denied exception.
	 */
	public function show() {
		if (!class_exists(WCFACP::class, false)) {
			BoxHandler::disablePageLayout();
			NoticeHandler::disableNotices();
		}
		SessionHandler::getInstance()->disableTracking();
		
		@header('HTTP/1.1 403 Forbidden');
		
		$name = get_class($this);
		$exceptionClassName = mb_substr($name, mb_strrpos($name, '\\') + 1);
		
		WCF::getTPL()->assign([
			'name' => get_class($this),
			'file' => $this->getFile(),
			'line' => $this->getLine(),
			'message' => $this->getMessage(),
			'stacktrace' => $this->getTraceAsString(),
			'templateName' => 'permissionDenied',
			'templateNameApplication' => 'wcf',
			'exceptionClassName' => $exceptionClassName,
			'isFirstVisit' => SessionHandler::getInstance()->isFirstVisit(),
		]);
		WCF::getTPL()->display('permissionDenied');
	}
}
