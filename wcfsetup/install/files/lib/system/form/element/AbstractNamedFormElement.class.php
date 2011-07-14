<?php
namespace wcf\system\form\element;
use wcf\util\StringUtil;

/**
 * Basic implementation for named form elements.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form
 * @category 	Community Framework
 */
abstract class AbstractNamedFormElement extends AbstractFormElement {
	/**
	 * element name
	 *
	 * @var	string
	 */
	protected $name = '';
	
	/**
	 * element value
	 *
	 * @var	string
	 */
	protected $value = '';
	
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
