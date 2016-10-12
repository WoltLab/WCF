<?php
namespace wcf\system\option;
use wcf\data\option\Option;

/**
 * Provides a default implementation for object types.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
abstract class AbstractOptionType implements IOptionType {
	/**
	 * internationalization support
	 * @var	boolean
	 */
	protected $supportI18n = false;
	
	/**
	 * @inheritDoc
	 */
	public function validate(Option $option, $newValue) {}
	
	/**
	 * @inheritDoc
	 */
	public function getData(Option $option, $newValue) {
		return $newValue;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getCSSClassName() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function supportI18n() {
		return $this->supportI18n;
	}
	
	/**
	 * @inheritDoc
	 */
	public function compare($value1, $value2) {
		return 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function hideLabelInSearch() {
		return false;
	}
}
