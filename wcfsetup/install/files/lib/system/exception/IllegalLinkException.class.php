<?php
namespace wcf\system\exception;
use wcf\system\WCF;

/**
 * IllegalLinkException shows the unknown link error page.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.exception
 * @category	Community Framework
 */
class IllegalLinkException extends NamedUserException {
	/**
	 * Creates a new IllegalLinkException object.
	 */
	public function __construct() {
		parent::__construct(WCF::getLanguage()->get('wcf.global.error.illegalLink'));
	}
	
	/**
	 * @see	\wcf\system\exception\NamedUserException::show();
	 */
	public function show() {
		@header('HTTP/1.0 404 Not Found');
		parent::show();
	}
}
