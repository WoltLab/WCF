<?php

namespace wcf\system\exception;
use wcf\util\StringUtil;

/**
 * A SystemException is thrown when an unexpected error occurs.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.exception
 * @category 	Community Framework
 */
class SystemException extends \Exception implements PrintableException {

	/**
	 * error description
	 * @var string
	 */
	protected $description = null;

	/**
	 * additional information
	 * @var string
	 */
	protected $information = '';

	/**
	 * additional information
	 * @var string
	 */
	protected $functions = '';

	/**
	 * Creates a new SystemException.
	 *
	 * @param	string		$message	error message
	 * @param	integer		$code		error code
	 * @param	string		$description	description of the error
	 */
	public function __construct($message = '', $code = 0, $description = '') {
		parent::__construct($message, $code);
		$this->description = $description;
	}

	/**
	 * Returns the description of this exception.
	 *
	 * @return 	string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Removes database password from stack trace.
	 * @see Exception::getTraceAsString()
	 */
	public function __getTraceAsString() {
		$string = preg_replace('/Database->__construct\(.*\)/', 'Database->__construct(...)', $this->getTraceAsString());
		$string = preg_replace('/mysqli->mysqli\(.*\)/', 'mysqli->mysqli(...)', $string);
		return $string;
	}

	/**
	 * @see PrintableException::show()
	 */
	public function show() {
		// send status code
		@header('HTTP/1.1 503 Service Unavailable');

		// print report
		echo '<?xml version="1.0" encoding="UTF-8"?>';

		?>

		<!DOCTYPE html>
		<html>
			<head>
				<title>Fatal error: <?php echo StringUtil::encodeHTML($this->getMessage()); ?></title>
				<style>
					.systemException {
						border: 1px outset lightgrey;
						padding: 3px;
						background-color: lightgrey;
						text-align: left;
						overflow: auto;
						font-family: Verdana, Helvetica, sans-serif;
						font-size: .8em;
					}
					.systemException div {
						border: 1px inset lightgrey;
						padding: 4px;
					}
					.systemException h1 {
						background-color: #154268;
						padding: 4px;
						color: #fff;
						margin: 0 0 3px 0;
						font-size: 1.15em;
						word-wrap: break-word;
					}
					.systemException h2 {
						font-size: 1.1em;
						margin-bottom: 0;
					}
					.systemException pre, .systemException p {
						margin: 0;
					}
					.systemException pre {
						font-size: .85em;
						font-family: "Courier New";
						text-overflow: ellipsis;
						overflow: hidden;
					}
					.systemException pre:hover{
						overflow: auto;
						text-overflow: clip;
					}
				</style>
			</head>
			<body>
				<div class="systemException">
					<h1>Fatal error: <?php echo StringUtil::encodeHTML($this->getMessage()); ?></h1>

					<div>
						<p><?php echo $this->getDescription(); ?></p>
						<?php if ($this->getCode()) { ?><p>You get more information about the problem in the official WoltLab knowledge base: <a href="http://www.woltlab.com/help/?code=<?php echo intval($this->getCode()); ?>">http://www.woltlab.com/help/?code=<?php echo intval($this->getCode()); ?></a></p><?php } ?>

						<h2>Information:</h2>
						<p>
							<b>error message:</b> <?php echo StringUtil::encodeHTML($this->getMessage()); ?><br>
							<b>error code:</b> <?php echo intval($this->getCode()); ?><br>
							<?php echo $this->information; ?>
							<b>file:</b> <?php echo StringUtil::encodeHTML($this->getFile()); ?> (<?php echo $this->getLine(); ?>)<br>
							<b>php version:</b> <?php echo StringUtil::encodeHTML(phpversion()); ?><br>
							<b>wcf version:</b> <?php echo WCF_VERSION; ?><br>
							<b>date:</b> <?php echo gmdate('r'); ?><br>
							<b>request:</b> <?php if (isset($_SERVER['REQUEST_URI']))  echo StringUtil::encodeHTML($_SERVER['REQUEST_URI']); ?><br>
							<b>referer:</b> <?php if (isset($_SERVER['HTTP_REFERER'])) echo StringUtil::encodeHTML($_SERVER['HTTP_REFERER']); ?><br>
						</p>

						<h2>Stacktrace:</h2>
						<pre><?php echo StringUtil::encodeHTML($this->__getTraceAsString()); ?></pre>
					</div>

				<?php echo $this->functions; ?>
				</div>
			</body>
		</html>

		<?php
	}

}
