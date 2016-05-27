<?php
namespace wcf\system\exception;

/**
 * Denotes an Exception with extra information for the human reader.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.exception
 * @category	Community Framework
 * @since	2.2
 */
interface IExtraInformationException {
	/**
	 * Returns an array of (key, value) tuples with extra information to show
	 * in the human readable error log.
	 * Avoid including sensitive information (such as private keys or passwords).
	 * 
	 * @return	mixed[][]
	 */
	public function getExtraInformation();
}
