<?php // @codingStandardsIgnoreFile
/**
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
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
	spl_autoload_register([WCF::class, 'autoload'], true, true);

	/**
	 * Helper method to output debug data for all passed variables,
	 * uses `print_r()` for arrays and objects, `var_dump()` otherwise.
	 *
	 * @return never
	 */
	function wcfDebug()
	{
		echo "<pre>";

		$args = func_get_args();
		$length = count($args);
		if ($length === 0) {
			echo "ERROR: No arguments provided.<hr>";
		} else {
			for ($i = 0; $i < $length; $i++) {
				$arg = $args[$i];

				echo "<h2>Argument {$i} (" . gettype($arg) . ")</h2>";

				if (is_array($arg) || is_object($arg)) {
					print_r($arg);
				} else {
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
			$_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF'])));
		}
		if (!isset($_SERVER['DOCUMENT_ROOT']) && isset($_SERVER['PATH_TRANSLATED'])) {
			$_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0 - strlen($_SERVER['PHP_SELF'])));
		}

		if (!isset($_SERVER['REQUEST_URI'])) {
			$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);
			if (isset($_SERVER['QUERY_STRING'])) {
				$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
			}
		}
	}
}

namespace wcf {
	function getRequestId(): string
	{
		if (!defined('WCF_REQUEST_ID_HEADER') || !WCF_REQUEST_ID_HEADER) return '';

		return $_SERVER[WCF_REQUEST_ID_HEADER] ?? '';
	}

	function getMinorVersion(): string
	{
		return preg_replace('/^(\d+\.\d+)\..*$/', '\\1', WCF_VERSION);
	}

	/**
	 * @deprecated 6.0 Use #[\SensitiveParameter] instead.
	 */
	#[\Attribute(\Attribute::TARGET_PARAMETER)]
	class SensitiveArgument {}
}

namespace wcf\functions\exception {

	use wcf\http\error\ExceptionLogger;
	use wcf\http\error\FatalExceptionRenderer;
	use wcf\util\StacktraceUtil;

	/**
	 * If the stacktrace contains a compiled template, the context of the relevant template line
	 * is returned, otherwise an empty array is returned.
	 *
	 * @return string[]
	 * @deprecated 6.3 Use StacktraceUtil::getTemplateContextLines() instead.
	 */
	function getTemplateContextLines(\Throwable $e): array
	{
		return StacktraceUtil::getTemplateContextLines($e);
	}

	/**
	 * Logs the given Throwable.
	 *
	 * @param	string			$logFile	The log file to use. If set to `null` the default log file will be used and the variable contents will be replaced by the actual path.
	 * @param-out string $logFile
	 * @return	string					The ID of the log entry.
	 * @deprecated 6.3 Use ExceptionLogger::log() instead.
	 */
	function logThrowable(\Throwable $e, &$logFile = null): string
	{
		return ExceptionLogger::log($e, $logFile);
	}

	/**
	 * Pretty prints the given Throwable. It is recommended to `exit;`
	 * the request after calling this function.
	 *
	 * @return void
	 * @throws \Exception
	 * @deprecated 6.3 Use FatalExceptionRenderer::render() instead.
	 */
	function printThrowable(\Throwable $e)
	{
		FatalExceptionRenderer::render($e);
	}

	/**
	 * Returns the stack trace of the given Throwable with sensitive
	 * information removed.
	 *
	 * @param	bool			$ignorePaths	If set to `true`: Don't call `sanitizePath`.
	 * @return	mixed[]
	 * @deprecated 6.3 Use StacktraceUtil::sanitize() instead.
	 */
	function sanitizeStacktrace(\Throwable $e, bool $ignorePaths = false)
	{
		return StacktraceUtil::sanitize($e, $ignorePaths);
	}

	/**
	 * @return mixed[]
	 * @deprecated 6.3 Use StacktraceUtil::getTraceWithoutIntermediateMiddleware() instead.
	 */
	function getTraceWithoutIntermediateMiddleware(\Throwable $e): array
	{
		return StacktraceUtil::getTraceWithoutIntermediateMiddleware($e);
	}

	/**
	 * Returns the given path relative to `WCF_DIR`, unless both,
	 * `EXCEPTION_PRIVACY` is `public` and the debug mode is enabled.
	 *
	 * @deprecated 6.3 Use StacktraceUtil::sanitizePath() instead.
	 */
	function sanitizePath(string $path, bool $removePath = true): string
	{
		return StacktraceUtil::sanitizePath($path, $removePath);
	}

	/**
	 * @deprecated 6.3 Use StacktraceUtil::formatPath() instead.
	 */
	function formatPath(string $path, int $lineNumber): string
	{
		return StacktraceUtil::formatPath($path, $lineNumber);
	}

	/**
	 * @param mixed[] $segment
	 * @deprecated 6.3 Use StacktraceUtil::isMiddlewareStart() instead.
	 */
	function isMiddlewareStart(array $segment): bool
	{
		return StacktraceUtil::isMiddlewareStart($segment);
	}

	/**
	 * @param mixed[] $segment
	 * @deprecated 6.3 Use StacktraceUtil::isMiddlewareEnd() instead.
	 */
	function isMiddlewareEnd(array $segment): bool
	{
		return StacktraceUtil::isMiddlewareEnd($segment);
	}
}
