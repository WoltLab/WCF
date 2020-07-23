<?php
namespace wcf\system\style\exception;

/**
 * Indicates that the font download failed.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Style
 * @since	5.3
 */
class FontDownloadFailed extends \Exception {
	/**
	 * @var string
	 */
	private $reason = '';
	
	public function __construct($message, $reason = '', \Throwable $previous = null) {
		parent::__construct($message, 0, $previous);
		
		$this->reason = $reason;
	}
	
	/**
	 * @return string
	 */
	public function getReason() {
		return $this->reason;
	}
}
