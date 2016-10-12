<?php
namespace wcf\system\captcha;

/**
 * Every captcha type has to implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Captcha
 */
interface ICaptchaHandler {
	/**
	 * Returns the form element.
	 * 
	 * @return	string
	 */
	public function getFormElement();
	
	/**
	 * Returns true if this kind of captcha is available.
	 * 
	 * @return	boolean
	 */
	public function isAvailable();
	
	/**
	 * Reads the parameters of the captcha form element.
	 */
	public function readFormParameters();
	
	/**
	 * Resets the captcha after it is no longer needed.
	 */
	public function reset();
	
	/**
	 * Validates the response to the challenge and marks the captcha as done.
	 */
	public function validate();
}
