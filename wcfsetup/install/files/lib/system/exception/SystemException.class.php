<?php
namespace wcf\system\exception;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * A SystemException is thrown when an unexpected error occurs.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.exception
 * @category	Community Framework
 */
// @codingStandardsIgnoreFile
class SystemException extends LoggedException implements IPrintableException {
	/**
	 * error description
	 * @var	string
	 */
	protected $description = null;
	
	/**
	 * additional information
	 * @var	string
	 */
	protected $information = '';
	
	/**
	 * additional information
	 * @var	string
	 */
	protected $functions = '';
	
	/**
	 * Creates a new SystemException.
	 * 
	 * @param	string		$message	error message
	 * @param	integer		$code		error code
	 * @param	string		$description	description of the error
	 * @param	\Exception	$previous	repacked Exception
	 */
	public function __construct($message = '', $code = 0, $description = '', \Exception $previous = null) {
		parent::__construct((string) $message, (int) $code, $previous);
		$this->description = $description;
	}
	
	/**
	 * Returns the description of this exception.
	 * 
	 * @return	string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * @see	\wcf\system\exception\IPrintableException::show()
	 */
	public function show() {
		// send status code
		@header('HTTP/1.1 503 Service Unavailable');
		
		// show user-defined system-exception
		if (defined('SYSTEMEXCEPTION_FILE') && file_exists(SYSTEMEXCEPTION_FILE)) {
			require(SYSTEMEXCEPTION_FILE);
			return;
		}
		
		$innerMessage = '';
		try {
			if (is_object(WCF::getLanguage())) {
				$innerMessage = WCF::getLanguage()->get('wcf.global.error.exception', true);
			}
		}
		catch (\Exception $e) { }
		
		if (empty($innerMessage)) {
			$innerMessage = 'Please send the ID above to the site administrator.<br />The error message can be looked up at &ldquo;ACP &raquo; Logs &raquo; Errors&rdquo;.';
		}
		
		// print report
		$e = ($this->getPrevious() ?: $this);
		?><!DOCTYPE html>
		<html>
			<head>
				<title>Fatal error: <?php echo StringUtil::encodeHTML($this->_getMessage()); ?></title>
				<meta charset="utf-8" />
				<style>
					.systemException {
						font-family: 'Trebuchet MS', Arial, sans-serif !important;
						font-size: 80% !important;
						text-align: left !important;
						border: 1px solid #036;
						border-radius: 7px;
						background-color: #eee !important;
						overflow: auto !important;
					}
					.systemException h1 {
						font-size: 130% !important;
						font-weight: bold !important;
						line-height: 1.1 !important;
						text-decoration: none !important;
						text-shadow: 0 -1px 0 #003 !important;
						color: #fff !important;
						word-wrap: break-word !important;
						border-bottom: 1px solid #036;
						border-top-right-radius: 6px;
						border-top-left-radius: 6px;
						background-color: #369 !important;
						margin: 0 !important;
						padding: 5px 10px !important;
					}
					.systemException div {
						border-top: 1px solid #fff;
						border-bottom-right-radius: 6px;
						border-bottom-left-radius: 6px;
						padding: 0 10px !important;
					}
					.systemException h2 {
						font-size: 130% !important;
						font-weight: bold !important;
						color: #369 !important;
						text-shadow: 0 1px 0 #fff !important;
						margin: 5px 0 !important;
					}
					.systemException pre, .systemException p {
						text-shadow: none !important;
						color: #555 !important;
						margin: 0 !important;
					}
					.systemException pre {
						font-size: .85em !important;
						font-family: "Courier New" !important;
						text-overflow: ellipsis;
						padding-bottom: 1px;
						overflow: hidden !important;
					}
					.systemException pre:hover{
						text-overflow: clip;
						overflow: auto !important;
					}
				</style>
			</head>
			<body>
				<div class="systemException">
					<h1>Fatal error: <?php if(!$this->getExceptionID()) { ?>Unable to write log file, please make &quot;<?php echo FileUtil::unifyDirSeparator(WCF_DIR); ?>log/&quot; writable!<?php } else { echo StringUtil::encodeHTML($this->_getMessage()); } ?></h1>
					
					<?php if (WCF::debugModeIsEnabled()) { ?>
						<div>
							<?php if ($this->getDescription()) { ?><p><br /><?php echo $this->getDescription(); ?></p><?php } ?>
							
							<h2>Information:</h2>
							<p>
								<b>id:</b> <code><?php echo $this->getExceptionID(); ?></code><br>
								<b>error message:</b> <?php echo StringUtil::encodeHTML($this->_getMessage()); ?><br>
								<b>error code:</b> <?php echo intval($e->getCode()); ?><br>
								<?php echo $this->information; ?>
								<b>file:</b> <?php echo StringUtil::encodeHTML($e->getFile()); ?> (<?php echo $e->getLine(); ?>)<br>
								<b>php version:</b> <?php echo StringUtil::encodeHTML(phpversion()); ?><br>
								<b>wcf version:</b> <?php echo WCF_VERSION; ?><br>
								<b>date:</b> <?php echo gmdate('r'); ?><br>
								<b>request:</b> <?php if (isset($_SERVER['REQUEST_URI'])) echo StringUtil::encodeHTML($_SERVER['REQUEST_URI']); ?><br>
								<b>referer:</b> <?php if (isset($_SERVER['HTTP_REFERER'])) echo StringUtil::encodeHTML($_SERVER['HTTP_REFERER']); ?><br>
							</p>
							
							<h2>Stacktrace:</h2>
							<pre><?php echo StringUtil::encodeHTML($this->__getTraceAsString()); ?></pre>
						</div>
					<?php } else { ?>
						<div>
							<h2>Information:</h2>
							<p>
								<?php if (!$this->getExceptionID()) { ?>
									Unable to write log file, please make &quot;<?php echo FileUtil::unifyDirSeparator(WCF_DIR); ?>log/&quot; writable!
								<?php } else { ?>
									<b>ID:</b> <code><?php echo $this->getExceptionID(); ?></code><br>
									<?php echo $innerMessage; ?>
								<?php } ?>
							</p>
						</div>
					<?php } ?>
					
					<?php echo $this->functions; ?>
				</div>
			</body>
		</html>
		
		<?php
	}
}
