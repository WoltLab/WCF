<?php
namespace wcf\system\form\element;
use wcf\util\StringUtil;

/**
 * Basic implementation for named form elements.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Element
 */
abstract class AbstractNamedFormElement extends AbstractFormElement {
	/**
	 * element description
	 * @var	string
	 */
	protected $description = '';
	
	/**
	 * element name
	 * @var	string
	 */
	protected $name = '';
	
	/**
	 * element value
	 * @var	string
	 */
	protected $value = '';
	
	/**
	 * Sets element description.
	 * 
	 * @param	string		$description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}
	
	/**
	 * Returns element description.
	 * 
	 * @return	string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * Sets element name.
	 * 
	 * @param	string		$name
	 */
	public function setName($name) {
		$this->name = StringUtil::trim($name);
	}
	
	/**
	 * Returns element name
	 * 
	 * @return	string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Sets element value.
	 * 
	 * @param	string		$value
	 */
	public function setValue($value) {
		if (!is_string($value)) {
			die(print_r($value, true));
		}
		$this->value = StringUtil::trim($value);
	}
	
	/**
	 * Returns element value.
	 * 
	 * @return	string
	 */
	public function getValue() {
		return $this->value;
	}
}
