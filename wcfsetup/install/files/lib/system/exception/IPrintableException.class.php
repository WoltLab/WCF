<?php
namespace wcf\system\exception;

/**
 * WCF::handleException() calls the show method on exceptions that implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Exception
 * @deprecated	3.0 - Fatal Exceptions are printed automatically, if you need a well styled page use: NamedUserException
 */
interface IPrintableException {
	/**
	 * Prints this exception.
	 * This method is called by WCF::handleException().
	 */
	public function show();
}
