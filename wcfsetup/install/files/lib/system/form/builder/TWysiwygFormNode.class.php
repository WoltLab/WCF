<?php
namespace wcf\system\form\builder;

/**
 * Provides methods to get and set the id of the related `WysiwygFormField` form field for wysiwyg-
 * related form nodes.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Container\Wysiwyg
 * @since	5.2
 */
trait TWysiwygFormNode {
	/**
	 * id of the related `WysiwygFormField` form field
	 * @var	string
	 */
	protected $wysiwygId;
	
	/**
	 * Returns the prefixed id of the related `WysiwygFormField` form field.
	 * 
	 * @return	string
	 * @throws	\BadMethodCallException		if the id of the related `WysiwygFormField` form field is unknown
	 */
	public function getPrefixedWysiwygId() {
		return $this->getDocument()->getPrefix() . $this->getWysiwygId();
	}
	
	/**
	 * Returns id of the related `WysiwygFormField` form field.
	 * 
	 * @return	string
	 * @throws	\BadMethodCallException		if the id of the related `WysiwygFormField` form field is unknown
	 */
	public function getWysiwygId() {
		if ($this->wysiwygId === null) {
			throw new \BadMethodCallException("The id of the related 'WysiwygFormField' form field is unknown.");
		}
		
		return $this->wysiwygId;
	}
	
	/**
	 * Sets the id of the related `WysiwygFormField` form field and returns this field.
	 * 
	 * @param	string		$wysiwygId
	 * @return	static				this field
	 */
	public function wysiwygId($wysiwygId) {
		$this->wysiwygId = $wysiwygId;
		
		return $this;
	}
}
