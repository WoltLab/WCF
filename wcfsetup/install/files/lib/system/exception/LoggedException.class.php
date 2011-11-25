<?php
namespace wcf\system\exception;
use wcf\system\WCF;

/**
 * A logged exceptions prevents information disclosures and provides an easy
 * way to log errors.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.exception
 * @category 	Community Framework
 */
class LoggedException extends \Exception {
	/**
	 * Surpresses the original error message.
	 * 
	 * @see		\Exception::getMessage()
	 */
	public function _getMessage() {
		if (!WCF::debugModeIsEnabled()) {
			return 'An error occured. Sorry.';
		}
		
		return $this->getMessage();
	}
	
	/**
	 * Writes an error to log file.
	 */
	protected function logError() {
		$logFile = WCF_DIR . 'log/' . date('Y-m-d', TIME_NOW) . '.txt';
		
		// try to create file
		@touch($logFile);
		
		// validate if file exists and is accessible for us
		if (!file_exists($logFile) || !is_writable($logFile)) {
			/*
				We cannot recover if we reached this point, the server admin
				is urged to fix his pretty much broken configuration.
				
				GLaDOS: Look at you, sailing through the air majestically, like an eagle... piloting a blimp.
			*/
			return;
		}
		
		// build message
		$message = date('r', TIME_NOW) . "\n" . $this->getMessage() . "\n\n" . $this->getTraceAsString() . "\n\n\n";
		
		// append
		@file_put_contents($logFile, $message, FILE_APPEND);
	}
}
