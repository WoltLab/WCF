<?php
namespace wcf\system\setup;

/**
 * This exception is used to unwind the stack in CLIWCFSetup. It avoids side effects like an try-catch-statement catching the wrong exception.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system
 * @category	Community Framework
 */
class CLIGotoNextStep extends \Exception {
	/**
	 * step to execute next
	 * @var string
	 */
	private $nextStep = '';
	
	/**
	 * Saves the next step for later use
	 * 
	 * @param string $nextStep
	 */
	public function __construct($nextStep) {
		$this->nextStep = $nextStep;
	}
	
	/**
	 * Returns the next step
	 * 
	 * @return string
	 */
	public function getNextStep() {
		return $this->nextStep;
	}
}
