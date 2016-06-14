<?php
namespace wcf\system\exception;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;

/**
 * A PermissionDeniedException is thrown when a user has no permission to access
 * to a specific area.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Exception
 */
class PermissionDeniedException extends UserException {
	/**
	 * Creates a new PermissionDeniedException object.
	 */
	public function __construct() {
		parent::__construct(WCF::getLanguage()->get('wcf.global.error.permissionDenied'));
	}
	
	/**
	 * Prints a permission denied exception.
	 */
	public function show() {
		SessionHandler::getInstance()->disableTracking();
		
		@header('HTTP/1.0 403 Forbidden');
		
		WCF::getTPL()->assign([
			'name' => get_class($this),
			'file' => $this->getFile(),
			'line' => $this->getLine(),
			'stacktrace' => $this->getTraceAsString(),
			'templateName' => 'permissionDenied',
			'templateNameApplication' => 'wcf'
		]);
		WCF::getTPL()->display('permissionDenied');
	}
}
