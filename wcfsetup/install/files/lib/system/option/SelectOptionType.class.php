<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\WCF;

/**
 * Option type implementation for select lists.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class SelectOptionType extends RadioButtonOptionType {
	protected $allowEmptyValue = false;
	
	/**
	 * @see	\wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		$options = $this->parseEnableOptions($option);
		
		WCF::getTPL()->assign(array(
			'disableOptions' => $options['disableOptions'],
			'enableOptions' => $options['enableOptions'],
			'option' => $option,
			'selectOptions' => $this->getSelectOptions($option),
			'value' => $value,
			'allowEmptyValue' => ($this->allowEmptyValue || $option->allowEmptyValue)
		));
		return WCF::getTPL()->fetch('selectOptionType');
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableUserOption::getSearchFormElement()
	 */
	public function getSearchFormElement(Option $option, $value) {
		$options = $this->parseEnableOptions($option);
		
		WCF::getTPL()->assign(array(
			'disableOptions' => $options['disableOptions'],
			'enableOptions' => $options['enableOptions'],
			'option' => $option,
			'searchOption' => ($value !== null && $value !== $option->defaultValue) || isset($_POST['searchOptions'][$option->optionName]),
			'selectOptions' => $this->getSelectOptions($option),
			'value' => $value
		));
		return WCF::getTPL()->fetch('selectSearchableOptionType');
	}
	
	/**
	 * Prepares JSON-encoded values for disabling or enabling dependent options.
	 * 
	 * @param	\wcf\data\option\Option	$option
	 * @return	array
	 */
	protected function parseEnableOptions(Option $option) {
		$disableOptions = $enableOptions = '';
		
		if (!empty($option->enableOptions)) {
			$options = $option->parseMultipleEnableOptions();
			
			foreach ($options as $key => $optionData) {
				$tmp = explode(',', $optionData);
				
				foreach ($tmp as $item) {
					if ($item{0} == '!') {
						if (!empty($disableOptions)) $disableOptions .= ',';
						$disableOptions .= "{ value: '".$key."', option: '".mb_substr($item, 1)."' }";
					}
					else {
						if (!empty($enableOptions)) $enableOptions .= ',';
						$enableOptions .= "{ value: '".$key."', option: '".$item."' }";
					}
				}
			}
		}
		
		return array(
			'disableOptions' => $disableOptions,
			'enableOptions' => $enableOptions
		);
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::hideLabelInSearch()
	 */
	public function hideLabelInSearch() {
		return true;
	}
}
