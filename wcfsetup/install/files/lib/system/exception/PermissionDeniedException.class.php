<?php
namespace wcf\system\exception;
use wcf\system\WCF;

/**
 * A PermissionDeniedException is thrown when a user has no permission to access to a specific area.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.exception
 * @category 	Community Framework
 */
class PermissionDeniedException extends UserException {
	/**
	 * Creates a new IllegalLinkException object.
	 */
	public function __construct() {
		parent::__construct(WCF::getLanguage()->get('wcf.global.error.permissionDenied'));
	}

	/**
	 * Prints a permission denied exception.
	 */
	public function show() {
		@header('HTTP/1.0 403 Forbidden');
		WCF::getTPL()->display('permissionDenied');
	}
}
