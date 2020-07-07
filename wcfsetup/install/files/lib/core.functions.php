<?php
/**
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */
namespace {
	use wcf\system\WCF;

	// set exception handler
	set_exception_handler([WCF::class, 'handleException']);
	// set php error handler
	set_error_handler([WCF::class, 'handleError'], E_ALL);
	// set shutdown function
	register_shutdown_function([WCF::class, 'destruct']);
	// set autoload function
	spl_autoload_register([WCF::class, 'autoload']);
	
	spl_autoload_register(function ($className) {
		/**
		 * @deprecated 5.3 This file is a compatibility layer mapping from Leafo\\ to ScssPhp\\
		 */
		$leafo = 'Leafo\\';
		if (substr($className, 0, strlen($leafo)) === $leafo) {
			class_alias('ScssPhp\\'.substr($className, strlen($leafo)), $className, true);
		}
	});
	
	/**
	 * Escapes a string for use in sql query.
	 * 
	 * @see	\wcf\system\database\Database::escapeString()
	 * @param	string		$string
	 * @return	string
	 */
	function escapeString($string) {
		return WCF::getDB()->escapeString($string);
	}
	
	/**
	 * Helper method to output debug data for all passed variables,
	 * uses `print_r()` for arrays and objects, `var_dump()` otherwise.
	 */
	function wcfDebug() {
		echo "<pre>";
		
		$args = func_get_args();
		$length = count($args);
		if ($length === 0) {
			echo "ERROR: No arguments provided.<hr>";
		}
		else {
			for ($i = 0; $i < $length; $i++) {
				$arg = $args[$i];
				
				echo "<h2>Argument {$i} (" . gettype($arg) . ")</h2>";
				
				if (is_array($arg) || is_object($arg)) {
					print_r($arg);
				}
				else {
					var_dump($arg);
				}
				
				echo "<hr>";
			}
		}
		
		$backtrace = debug_backtrace();
		
		// output call location to help finding these debug outputs again
		echo "wcfDebug() called in {$backtrace[0]['file']} on line {$backtrace[0]['line']}";
		
		echo "</pre>";
		
		exit;
	}

	// define DOCUMENT_ROOT on IIS if not set
	if (PHP_EOL == "\r\n") {
		if (!isset($_SERVER['DOCUMENT_ROOT']) && isset($_SERVER['SCRIPT_FILENAME'])) {
			$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF'])));
		}
		if (!isset($_SERVER['DOCUMENT_ROOT']) && isset($_SERVER['PATH_TRANSLATED'])) {
			$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0 - strlen($_SERVER['PHP_SELF'])));
		}

		if (!isset($_SERVER['REQUEST_URI'])) {
			$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);
			if (isset($_SERVER['QUERY_STRING'])) {
				$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
			}
		}
	}
	
	// setting global gzip compression breaks output buffering
	if (@ini_get('zlib.output_compression')) {
		@ini_set('zlib.output_compression', '0');
	}
	
	if (!function_exists('is_countable')) {
		function is_countable($var) {
			return is_array($var) || $var instanceof Countable || $var instanceof ResourceBundle || $var instanceof SimpleXmlElement;
		}
	}
}

// @codingStandardsIgnoreStart
namespace wcf {
	function getRequestId() {
		if (!defined('WCF_REQUEST_ID_HEADER') || !WCF_REQUEST_ID_HEADER) return '';
		
		return $_SERVER[WCF_REQUEST_ID_HEADER] ?? '';
	}
}

namespace wcf\functions\exception {
	use wcf\system\WCF;
	use wcf\system\exception\IExtraInformationException;
	use wcf\system\exception\SystemException;
	use wcf\util\FileUtil;
	use wcf\util\StringUtil;
	
	/**
	 * Logs the given Throwable.
	 * 
	 * @param	\Throwable|\Exception	$e
	 * @param	string			$logFile	The log file to use. If set to `null` the default log file will be used and the variable contents will be replaced by the actual path.
	 * @return	string					The ID of the log entry.
	 */
	function logThrowable($e, &$logFile = null) {
		if ($logFile === null) $logFile = WCF_DIR . 'log/' . gmdate('Y-m-d', TIME_NOW) . '.txt';
		touch($logFile);
		
		$stripNewlines = function ($item) {
			return str_replace("\n", ' ', $item);
		};
		
		// don't forget to update ExceptionLogUtil / ExceptionLogViewPage, when changing the log file format
		$message = gmdate('r', TIME_NOW)."\n".
			'Message: '.$stripNewlines($e->getMessage())."\n".
			'PHP version: '.phpversion()."\n".
			'WoltLab Suite version: '.WCF_VERSION."\n".
			'Request URI: '.$stripNewlines(($_SERVER['REQUEST_METHOD'] ?? '').' '.($_SERVER['REQUEST_URI'] ?? '')).(\wcf\getRequestId() ? ' ('.\wcf\getRequestId().')' : '')."\n".
			'Referrer: '.$stripNewlines($_SERVER['HTTP_REFERER'] ?? '')."\n".
			'User Agent: '.$stripNewlines($_SERVER['HTTP_USER_AGENT'] ?? '')."\n".
			'Peak Memory Usage: '.memory_get_peak_usage().'/'.FileUtil::getMemoryLimit()."\n";
		$prev = $e;
		do {
			$message .= "======\n".
			'Error Class: '.get_class($prev)."\n".
			'Error Message: '.$stripNewlines($prev->getMessage())."\n".
			'Error Code: '.$stripNewlines($prev->getCode())."\n".
			'File: '.$stripNewlines($prev->getFile()).' ('.$prev->getLine().')'."\n".
			'Extra Information: '.($prev instanceof IExtraInformationException ? base64_encode(serialize($prev->getExtraInformation())) : '-')."\n".
			'Stack Trace: '.json_encode(array_map(function ($item) {
				$item['args'] = array_map(function ($item) {
					switch (gettype($item)) {
						case 'object':
							return get_class($item);
						case 'array':
							return array_map(function () {
								return '[redacted]';
							}, $item);
						case 'resource':
							return 'resource('.get_resource_type($item).')';
						default:
							return $item;
					}
				}, $item['args']);
				
				return $item;
			}, sanitizeStacktrace($prev, true)))."\n";
		}
		while ($prev = $prev->getPrevious());
		
		// calculate Exception-ID
		$exceptionID = sha1($message);
		$entry = "<<<<<<<<".$exceptionID."<<<<\n".$message."<<<<\n\n";
		
		file_put_contents($logFile, $entry, FILE_APPEND);

		// let the Exception know it has been logged
		if (method_exists($e, 'finalizeLog') && is_callable([$e, 'finalizeLog'])) $e->finalizeLog($exceptionID, $logFile);

		return $exceptionID;
	}

	/**
	 * Pretty prints the given Throwable. It is recommended to `exit;`
	 * the request after calling this function.
	 * 
	 * @param	\Throwable|\Exception	$e
	 * @throws	\Exception
	 */
	function printThrowable($e) {
		$exceptionID = logThrowable($e, $logFile);
		if (\wcf\getRequestId()) $exceptionID .= '/'.\wcf\getRequestId();
		
		$exceptionTitle = $exceptionSubtitle = $exceptionExplanation = '';
		$logFile = sanitizePath($logFile);
		try {
			if (WCF::getLanguage() !== null) {
				$exceptionTitle = WCF::getLanguage()->get('wcf.global.exception.title', true);
				$exceptionSubtitle = str_replace('{$exceptionID}', $exceptionID, WCF::getLanguage()->get('wcf.global.exception.subtitle', true));
				$exceptionExplanation = str_replace('{$logFile}', $logFile, WCF::getLanguage()->get('wcf.global.exception.explanation', true));
			}
		}
		catch (\Throwable $e) {
			// ignore
		}
		
		if (!$exceptionTitle || !$exceptionSubtitle || !$exceptionExplanation) {
			// one or more failed, fallback to english
			$exceptionTitle = 'An error has occurred';
			$exceptionSubtitle = 'Internal error code: <span class="exceptionInlineCodeWrapper"><span class="exceptionInlineCode">'.$exceptionID.'</span></span>';
			$exceptionExplanation = <<<EXPLANATION
<p class="exceptionSubtitle">What happened?</p>
<p class="exceptionText">An error has occured while trying to handle your request and execution has been terminated. Please forward the above error code to the site administrator.</p>
<p class="exceptionText">&nbsp;</p> <!-- required to ensure spacing after copy & paste -->
<p class="exceptionText">
	The error code can be used by an administrator to lookup the full error message in the Administration Control Panel via “Logs » Errors”.
	In addition the error has been written to the log file located at <span class="exceptionInlineCodeWrapper"><span class="exceptionInlineCode">{$logFile}</span></span> and can be accessed with a FTP program or similar.
</p>
<p class="exceptionText">&nbsp;</p> <!-- required to ensure spacing after copy & paste -->
<p class="exceptionText">Notice: The error code was randomly generated and has no use beyond looking up the full message.</p>
EXPLANATION;

		}
		
		/*
		 * A notice on the HTML used below:
		 * 
		 * It might appear a bit weird to use <p> all over the place where semantically
		 * other elements would fit in way better. The reason behind this is that we avoid
		 * inheriting unwanted styles (e.g. exception displayed in an overlay) and that
		 * the output needs to be properly readable when copied & pasted somewhere.
		 * 
		 * Besides the visual appearance, the output was built to provide a maximum of
		 * compatibility and readability when pasted somewhere else, e.g. a WYSIWYG editor
		 * without the potential of messing up the formatting and thus harming the readability.
		 */
	?><!DOCTYPE html>
	<html>
		<head>
			<?php if (!defined('EXCEPTION_PRIVACY') || EXCEPTION_PRIVACY !== 'private') { ?>
			<title>Fatal Error: <?php echo StringUtil::encodeHTML($e->getMessage()); ?></title>
			<?php } else { ?>
			<title>Fatal Error</title>
			<?php } ?>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<style>
				.exceptionBody {
					background-color: rgb(250, 250, 250);
					color: rgb(44, 62, 80);
					margin: 0;
					padding: 0;
				}
				
				.exceptionContainer {
					box-sizing: border-box;
					font-family: 'Segoe UI', 'Lucida Grande', 'Helvetica Neue', Helvetica, Arial, sans-serif;
					font-size: 14px;
					padding-bottom: 20px;
				}
				
				.exceptionContainer * {
					box-sizing: inherit;
					line-height: 1.5em;
					margin: 0;
					padding: 0;
				}
				
				.exceptionHeader {
					background-color: rgb(58, 109, 156);
					padding: 30px 0;
				}
				
				.exceptionTitle {
					color: #fff;
					font-size: 28px;
					font-weight: 300;
				}
				
				.exceptionErrorCode {
					color: #fff;
					margin-top: .5em;
				}
				
				.exceptionErrorCode .exceptionInlineCode {
					background-color: rgb(43, 79, 113);
					border-radius: 3px;
					color: #fff;
					font-family: monospace;
					padding: 3px 10px;
					white-space: nowrap;
				}
				
				.exceptionSubtitle {
					border-bottom: 1px solid rgb(238, 238, 238);
					font-size: 24px;
					font-weight: 300;
					margin-bottom: 15px;
					padding-bottom: 10px;
				}
				
				.exceptionContainer > .exceptionBoundary {
					margin-top: 30px;
				}
				
				.exceptionText .exceptionInlineCodeWrapper {
					border: 1px solid rgb(169, 169, 169);
					border-radius: 3px;
					padding: 2px 5px;
				}
				
				.exceptionText .exceptionInlineCode {
					font-family: monospace;
					white-space: nowrap;
				}
				
				.exceptionFieldTitle {
					color: rgb(59, 109, 169);
				}
				
				.exceptionFieldTitle .exceptionColon {
					/* hide colon in browser, but will be visible after copy & paste */
					opacity: 0;
				}
				
				.exceptionFieldValue {
					font-size: 18px;
					min-height: 1.5em;
				}
				
				.exceptionSystemInformation,
				.exceptionErrorDetails,
				.exceptionStacktrace {
					list-style-type: none;
				}
				
				.exceptionSystemInformation > li:not(:first-child),
				.exceptionErrorDetails > li:not(:first-child) {
					margin-top: 10px;
				}
				
				.exceptionStacktrace {
					display: block;
					margin-top: 5px;
					overflow: auto;
					padding-bottom: 20px;
				}
				
				.exceptionStacktraceFile,
				.exceptionStacktraceFile span,
				.exceptionStacktraceCall,
				.exceptionStacktraceCall span {
					font-family: monospace !important;
					white-space: nowrap !important;
				}
				
				.exceptionStacktraceCall + .exceptionStacktraceFile {
					margin-top: 5px;
				}
				
				.exceptionStacktraceCall {
					padding-left: 40px;
				}
				
				.exceptionStacktraceCall,
				.exceptionStacktraceCall span {
					color: rgb(102, 102, 102) !important;
					font-size: 13px !important;
				}
				
				/* mobile */
				@media (max-width: 767px) {
					.exceptionBoundary {
						min-width: 320px;
						padding: 0 10px;
					}
					
					.exceptionText .exceptionInlineCodeWrapper {
						display: inline-block;
						overflow: auto;
					}
					
					.exceptionErrorCode .exceptionInlineCode {
						font-size: 13px;
						padding: 2px 5px;
					}
				}
				
				/* desktop */
				@media (min-width: 768px) {
					.exceptionBoundary {
						margin: 0 auto;
						max-width: 1400px;
						min-width: 1200px;
						padding: 0 10px;
					}
					
					.exceptionSystemInformation {
						display: flex;
						flex-wrap: wrap;
					}
					
					.exceptionSystemInformation1,
					.exceptionSystemInformation3,
					.exceptionSystemInformation5 {
						flex: 0 0 200px;
						margin: 0 0 10px 0 !important;
					}
					
					.exceptionSystemInformation2,
					.exceptionSystemInformation4,
					.exceptionSystemInformation6 {
						flex: 0 0 calc(100% - 210px);
						margin: 0 0 10px 10px !important;
						max-width: calc(100% - 210px);
					}
					
					.exceptionSystemInformation1 { order: 1; }
					.exceptionSystemInformation2 { order: 2; }
					.exceptionSystemInformation3 { order: 3; }
					.exceptionSystemInformation4 { order: 4; }
					.exceptionSystemInformation5 { order: 5; }
					.exceptionSystemInformation6 { order: 6; }
					
					.exceptionSystemInformation .exceptionFieldValue {
						overflow: hidden;
						text-overflow: ellipsis;
						white-space: nowrap;
					}
				}
			</style>
		</head>
		<body class="exceptionBody">
			<div class="exceptionContainer">
				<div class="exceptionHeader">
					<div class="exceptionBoundary">
						<p class="exceptionTitle"><?php echo $exceptionTitle; ?></p>
						<p class="exceptionErrorCode"><?php echo str_replace('{$exceptionID}', $exceptionID, $exceptionSubtitle); ?></p>
					</div>
				</div>
				
				<div class="exceptionBoundary">
					<?php echo $exceptionExplanation; ?>
				</div>
				<?php if (!defined('EXCEPTION_PRIVACY') || EXCEPTION_PRIVACY !== 'private') { ?>
					<div class="exceptionBoundary">
						<p class="exceptionSubtitle">System Information</p>
						<ul class="exceptionSystemInformation">
							<li class="exceptionSystemInformation1">
								<p class="exceptionFieldTitle">PHP Version<span class="exceptionColon">:</span></p>
								<p class="exceptionFieldValue"><?php echo StringUtil::encodeHTML(phpversion()); ?></p>
							</li>
							<li class="exceptionSystemInformation3">
								<p class="exceptionFieldTitle">WoltLab Suite Core<span class="exceptionColon">:</span></p>
								<p class="exceptionFieldValue"><?php echo StringUtil::encodeHTML(WCF_VERSION); ?></p>
							</li>
							<li class="exceptionSystemInformation5">
								<p class="exceptionFieldTitle">Peak Memory Usage<span class="exceptionColon">:</span></p>
								<p class="exceptionFieldValue"><?php echo round(memory_get_peak_usage() / 1024 / 1024, 3); ?>/<?php echo round(FileUtil::getMemoryLimit() / 1024 / 1024, 3); ?> MiB</p>
							</li>
							<li class="exceptionSystemInformation2">
								<p class="exceptionFieldTitle">Request URI<span class="exceptionColon">:</span></p>
								<p class="exceptionFieldValue"><?php if (isset($_SERVER['REQUEST_METHOD'])) echo StringUtil::encodeHTML($_SERVER['REQUEST_METHOD']); ?> <?php if (isset($_SERVER['REQUEST_URI'])) echo StringUtil::encodeHTML($_SERVER['REQUEST_URI']); ?></p>
							</li>
							<li class="exceptionSystemInformation4">
								<p class="exceptionFieldTitle">Referrer<span class="exceptionColon">:</span></p>
								<p class="exceptionFieldValue"><?php if (isset($_SERVER['HTTP_REFERER'])) echo StringUtil::encodeHTML($_SERVER['HTTP_REFERER']); ?></p>
							</li>
							<li class="exceptionSystemInformation6">
								<p class="exceptionFieldTitle">User Agent<span class="exceptionColon">:</span></p>
								<p class="exceptionFieldValue"><?php if (isset($_SERVER['HTTP_USER_AGENT'])) echo StringUtil::encodeHTML($_SERVER['HTTP_USER_AGENT']); ?></p>
							</li>
						</ul>
					</div>
					
					<?php
					$first = true;
					$exceptions = [];
					$current = $e;
					do {
						$exceptions[] = $current;
					}
					while ($current = $current->getPrevious());
					
					$e = array_pop($exceptions);
					do {
					?>
					<div class="exceptionBoundary">
						<p class="exceptionSubtitle"><?php if (!empty($exceptions) && $first) { echo "Original "; } else if (empty($exceptions) && !$first) { echo "Final "; } ?>Error</p>
						<?php if ($e instanceof SystemException && $e->getDescription()) { ?>
							<p class="exceptionText"><?php echo $e->getDescription(); ?></p>
						<?php } ?>
						<ul class="exceptionErrorDetails">
							<li>
								<p class="exceptionFieldTitle">Error Type<span class="exceptionColon">:</span></p>
								<p class="exceptionFieldValue"><?php echo StringUtil::encodeHTML(get_class($e)); ?></p>
							</li>
							<li>
								<p class="exceptionFieldTitle">Error Message<span class="exceptionColon">:</span></p>
								<p class="exceptionFieldValue"><?php echo StringUtil::encodeHTML($e->getMessage()); ?></p>
							</li>
							<?php if ($e->getCode()) { ?>
								<li>
									<p class="exceptionFieldTitle">Error Code<span class="exceptionColon">:</span></p>
									<p class="exceptionFieldValue"><?php echo StringUtil::encodeHTML($e->getCode()); ?></p>
								</li>
							<?php } ?>
							<li>
								<p class="exceptionFieldTitle">File<span class="exceptionColon">:</span></p>
								<p class="exceptionFieldValue" style="word-break: break-all"><?php echo StringUtil::encodeHTML(sanitizePath($e->getFile())); ?> (<?php echo $e->getLine(); ?>)</p>
							</li>
							
							<?php
							if ($e instanceof SystemException) {
								ob_start();
								$e->show();
								ob_end_clean();

								$reflection = new \ReflectionClass($e);
								$property = $reflection->getProperty('information');
								$property->setAccessible(true);
								if ($property->getValue($e)) {
									throw new \Exception("Using the 'information' property of SystemException is not supported any more.");
								}
							}
							if ($e instanceof IExtraInformationException) {
								foreach ($e->getExtraInformation() as list($key, $value)) {
									?>
									<li>
										<p class="exceptionFieldTitle"><?php echo StringUtil::encodeHTML($key); ?><span class="exceptionColon">:</span></p>
										<p class="exceptionFieldValue"><?php echo StringUtil::encodeHTML($value); ?></p>
									</li>
									<?php
								}
							}
							?>
							<li>
								<p class="exceptionFieldTitle">Stack Trace<span class="exceptionColon">:</span></p>
								<ul class="exceptionStacktrace">
									<?php
									$trace = sanitizeStacktrace($e);
									for ($i = 0, $max = count($trace); $i < $max; $i++) {
										?>
										<li class="exceptionStacktraceFile"><?php echo '#'.$i.' '.StringUtil::encodeHTML($trace[$i]['file']).' ('.$trace[$i]['line'].')'.':'; ?></li>
										<li class="exceptionStacktraceCall">
										<?php
											echo $trace[$i]['class'].$trace[$i]['type'].$trace[$i]['function'].'(';
											echo implode(', ', array_map(function ($item) {
												switch (gettype($item)) {
													case 'integer':
													case 'double':
														return $item;
													case 'NULL':
														return 'null';
													case 'string':
														return "'".addcslashes(StringUtil::encodeHTML($item), "\\'")."'";
													case 'boolean':
														return $item ? 'true' : 'false';
													case 'array':
														$keys = array_keys($item);
														if (count($keys) > 5) return "[ ".count($keys)." items ]";
														return '[ '.implode(', ', array_map(function ($item) {
															return $item.' => ';
														}, $keys)).']';
													case 'object':
														return get_class($item);
													case 'resource':
														return 'resource('.get_resource_type($item).')';
													case 'resource (closed)':
														return 'resource (closed)';
												}
												
												throw new \LogicException('Unreachable');
											}, $trace[$i]['args']));
										echo ')</li>';
									}
									?>
								</ul>
							</li>
						</ul>
					</div>
					<?php
					$first = false;
					} while ($e = array_pop($exceptions));
					?>
				<?php } ?>
			</div>
		</body>
	</html>
	<?php
	}

	/**
	 * Returns the stack trace of the given Throwable with sensitive
	 * information removed.
	 * 
	 * @param	\Throwable|\Exception	$e
	 * @param	boolean			$ignorePaths	If set to `true`: Don't call `sanitizePath`.
	 * @return	mixed[]
	 */
	function sanitizeStacktrace($e, $ignorePaths = false) {
		$trace = $e->getTrace();

		return array_map(function ($item) use ($ignorePaths) {
			if (!isset($item['file'])) $item['file'] = '[internal function]';
			if (!isset($item['line'])) $item['line'] = '?';
			if (!isset($item['class'])) $item['class'] = '';
			if (!isset($item['type'])) $item['type'] = '';
			if (!isset($item['args'])) $item['args'] = [];
			
			// strip database credentials
			if (preg_match('~\\\\?wcf\\\\system\\\\database\\\\[a-zA-Z]*Database~', $item['class']) || $item['class'] === 'PDO') {
				if ($item['function'] === '__construct') {
					$item['args'] = array_map(function () {
						return '[redacted]';
					}, $item['args']);
				}
			}
			
			if (!$ignorePaths) {
				$item['args'] = array_map(function ($item) {
					if (!is_string($item)) return $item;
					
					if (preg_match('~^('.preg_quote($_SERVER['DOCUMENT_ROOT'], '~').'|'.preg_quote(WCF_DIR, '~').')~', $item)) {
						$item = sanitizePath($item);
					}

					return $item;
				}, $item['args']);
				
				$item['file'] = sanitizePath($item['file']);
			}
			
			return $item;
		}, $trace);
	}
	
	/**
	 * Returns the given path relative to `WCF_DIR`, unless both,
	 * `EXCEPTION_PRIVACY` is `public` and the debug mode is enabled.
	 * 
	 * @param	string		$path
	 * @return	string
	 */
	function sanitizePath($path) {
		if (WCF::debugModeIsEnabled() && defined('EXCEPTION_PRIVACY') && EXCEPTION_PRIVACY === 'public') {
			return $path;
		}
		
		return '*/'.FileUtil::removeTrailingSlash(FileUtil::getRelativePath(WCF_DIR, $path));
	}
}
// @codingStandardsIgnoreEnd
