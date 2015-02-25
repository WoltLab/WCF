<?php
namespace wcf\system\exception;

/**
 * WCF::handleException() calls the show method on exceptions that implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.exception
 * @category	Community Framework
 */
interface IPrintableException {
	/**
	 * Prints this exception.
	 * This method is called by WCF::handleException().
	 */
	public function show();
}
