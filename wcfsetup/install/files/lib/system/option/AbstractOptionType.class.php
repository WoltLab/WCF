<?php
namespace wcf\system\option;
use wcf\data\option\Option;

/**
 * Provides a default implementation for object types.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
abstract class AbstractOptionType implements IOptionType {
	/**
	 * internationalization support
	 * @var	boolean
	 */
	protected $supportI18n = false;
	
	/**
	 * @see	\wcf\system\option\IOptionType::validate()
	 */
	public function validate(Option $option, $newValue) {}
	
	/**
	 * @see	\wcf\system\option\IOptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		return $newValue;
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::getCSSClassName()
	 */
	public function getCSSClassName() {
		return '';
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::supportI18n()
	 */
	public function supportI18n() {
		return $this->supportI18n;
	}
}
