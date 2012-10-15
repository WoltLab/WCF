<?php
namespace wcf\system\exception;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * A logged exceptions prevents information disclosures and provides an easy
 * way to log errors.
 * 
 * @author	Tim Düsterhus, Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.exception
 * @category	Community Framework
 */
class LoggedException extends \Exception {
	/**
	 * @see	\Exception::getMessage()
	 */
	public function _getMessage() {
		// suppresses the original error message
		if (!WCF::debugModeIsEnabled()) {
			return 'An error occured. Sorry.';
		}
		
		$e = ($this->getPrevious() ?: $this);
		return $e->getMessage();
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
		
		$e = ($this->getPrevious() ?: $this);
		
		$message = date('r', TIME_NOW)."\n".
			'Message: '.$e->getMessage()."\n".
			'File: '.$e->getFile().' ('.$e->getLine().")\n".
			'PHP version: '.phpversion()."\n".
			'WCF version: '.WCF_VERSION."\n".
			'Request URI: '.(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '')."\n".
			'Referrer: '.(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '')."\n".
			"Stacktrace: \n  ".implode("\n  ", explode("\n", $e->getTraceAsString()))."\n";
		
		// calculate Exception-ID
		$id = StringUtil::getHash($message);
		$message = "<<<<<<<<".$id."<<<<\n".$message."<<<<\n\n";
		
		// append
		@file_put_contents($logFile, $message, FILE_APPEND);
		
		return $id;
	}
}
