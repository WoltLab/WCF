<?php
namespace wcf\action;

/**
 * All action classes should implement this interface.
 * An action executes a user input without showing a result page or a form. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	action
 * @category	Community Framework
 */
interface IAction {
	/**
	 * Initializes this action.
	 */
	public function __run();
	
	/**
	 * Reads the given parameters.
	 */
	public function readParameters();
	
	/**
	 * Executes this action.
	 */
	public function execute();
}
