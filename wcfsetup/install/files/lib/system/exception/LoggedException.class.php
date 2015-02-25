<?php
namespace wcf\system\exception;
use wcf\system\WCF;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * A logged exceptions prevents information disclosures and provides an easy
 * way to log errors.
 * 
 * @author	Tim Duesterhus, Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.exception
 * @category	Community Framework
 */
class LoggedException extends \Exception {
	/**
	 * exception id
	 * @var	string
	 */
	protected $exceptionID = '';
	
	/**
	 * ignore disabled debug mode
	 * @var	boolean
	 */
	protected $ignoreDebugMode = false;
	
	/**
	 * @see	\Exception::getMessage()
	 */
	public function _getMessage() {
		// suppresses the original error message
		if (!WCF::debugModeIsEnabled() && !$this->ignoreDebugMode) {
			return 'An error occured. Sorry.';
		}
		
		$e = ($this->getPrevious() ?: $this);
		return $e->getMessage();
	}
	
	/**
	 * Returns exception id
	 * 
	 * @return	string
	 */
	public function getExceptionID() {
		if (empty($this->exceptionID)) {
			$this->logError();
		}
		
		return $this->exceptionID;
	}
	
	/**
	 * Removes database password from stack trace.
	 * @see	\Exception::getTraceAsString()
	 */
	public function __getTraceAsString() {
		$e = ($this->getPrevious() ?: $this);
		$string = preg_replace('/Database->__construct\(.*\)/', 'Database->__construct(...)', $e->getTraceAsString());
		return $string;
	}
	
	/**
	 * Writes an error to log file.
	 */
	protected function logError() {
		if (!empty($this->exceptionID)) {
			return;
		}
		
		$logFile = WCF_DIR . 'log/' . gmdate('Y-m-d', TIME_NOW) . '.txt';
		
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
		
		// don't forget to update ExceptionLogViewPage, when changing the log file format
		$message = gmdate('r', TIME_NOW)."\n".
			'Message: '.$e->getMessage()."\n".
			'File: '.$e->getFile().' ('.$e->getLine().")\n".
			'PHP version: '.phpversion()."\n".
			'WCF version: '.WCF_VERSION."\n".
			'Request URI: '.(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '')."\n".
			'Referrer: '.(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '')."\n".
			'User-Agent: '.(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '')."\n".
			'Information: '.JSON::encode($this->information)."\n".
			"Stacktrace: \n  ".implode("\n  ", explode("\n", $this->__getTraceAsString()))."\n";
		
		// calculate Exception-ID
		$this->exceptionID = StringUtil::getHash($message);
		$message = "<<<<<<<<".$this->exceptionID."<<<<\n".$message."<<<<\n\n";
		
		// append
		@file_put_contents($logFile, $message, FILE_APPEND);
	}
}
