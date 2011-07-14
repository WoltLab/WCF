<?php
namespace wcf\util;

/**
 * Contains file-related functions.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category 	Community Framework
 */
class BasicFileUtil {
	/**
	 * Tries to find the temp folder.
	 * 
	 * @return	string
	 */
	public static function getTempFolder() {
		// use tmp folder in document root by default
		if (!empty($_SERVER['DOCUMENT_ROOT'])) {
			if (strpos($_SERVER['DOCUMENT_ROOT'], 'strato') !== false) {
				// strato bugfix
				// create tmp folder in document root automatically
				if (!@file_exists($_SERVER['DOCUMENT_ROOT'].'/tmp')) { 
					@mkdir($_SERVER['DOCUMENT_ROOT'].'/tmp/', 0777);
					@chmod($_SERVER['DOCUMENT_ROOT'].'/tmp/', 0777);
				}
			}
			if (@file_exists($_SERVER['DOCUMENT_ROOT'].'/tmp') && @is_writable($_SERVER['DOCUMENT_ROOT'].'/tmp')) {
				return $_SERVER['DOCUMENT_ROOT'].'/tmp/';
			}
		}
		
		if (isset($_ENV['TMP']) && @is_writable($_ENV['TMP'])) {
			return $_ENV['TMP'] . '/';
		}
		if (isset($_ENV['TEMP']) && @is_writable($_ENV['TEMP'])) {
			return $_ENV['TEMP'] . '/';
		}
		if (isset($_ENV['TMPDIR']) && @is_writable($_ENV['TMPDIR'])) {
			return $_ENV['TMPDIR'] . '/';
		}
		
		// workaround for a bug in php 5.1.2 that returns true for is_writable('/tmp/') with safe_mode = on
		if (!preg_match('/^5\.1\.2(?![.0-9])/', phpversion())) {
			if (($path = ini_get('upload_tmp_dir')) && @is_writable($path)) {
				return $path . '/';
			}
			if (@file_exists('/tmp/') && @is_writable('/tmp/')) {
				return '/tmp/';
			}
			if (function_exists('session_save_path') && ($path = session_save_path()) && @is_writable($path)) {
				return $path . '/';
			}
		}
		
		$path = WCF_DIR.'tmp/';
		if (@file_exists($path) && @is_writable($path)) {
			return $path;
		}
		else {
			if (ini_get('safe_mode')) $reason = "due to php safe_mode restrictions";
			else $reason = "due to an unknown reason";
			throw new SystemException('There is no access to the system temporary folder '.$reason.' and no user specific temporary folder exists in '.WCF_DIR.'! This is a misconfiguration of your webserver software! Please create a folder called '.$path.' using your favorite ftp program, make it writable and then retry this installation.', 10000);
		}
	}
}
