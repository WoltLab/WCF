<?php
namespace wcf\system\form\builder\button;
use wcf\system\form\builder\IFormChildNode;
use wcf\system\form\builder\IFormElement;

/**
 * Represents a form button that is shown at the end of the form.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Button
 * @since	5.2
 */
interface IFormButton extends IFormChildNode, IFormElement {
	/**
	 * Sets the access key for this form button and returns this form button. If `null` is passed,
	 * the previously set access key is unset.
	 * 
	 * @param	null|string	$accessKey	button access key
	 * @return	static				this form button
	 * @throws	\InvalidArgumentException	if the given access key is invalid
	 */
	public function accessKey($accessKey = null);
	
	/**
	 * Returns the access key for this form button or `null` if no access key has been set.
	 * 
	 * By default, no access key is set.
	 * 
	 * @return	null|string
	 */
	public function getAccessKey();
	
	/**
	 * Returns `true` this button is an `input[type=submit]` element and `false` if it is a `button`
	 * element.
	 * 
	 * @return	boolean
	 */
	public function isSubmit();
	
	/**
	 * Sets whether this button is an `input[type=submit]` element or a `button` element. 
	 * 
	 * @param	boolean		$submitButton
	 * @return	static				this form button
	 */
	public function submit($submitButton = true);
}
