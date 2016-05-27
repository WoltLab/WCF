<?php
namespace wcf\util\exception;

/**
 * Denotes failure to perform secure crypto.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util.exception
 * @category	Community Framework
 * @since	2.2
 */
class CryptoException extends \Exception {
	/**
	 * @inheritDoc
	 */
	public function __construct($message, $previous = null) {
		parent::__construct($message, 0, $previous);
	}
}
