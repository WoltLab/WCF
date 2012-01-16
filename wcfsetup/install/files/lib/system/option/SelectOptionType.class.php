<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\WCF;

/**
 * SelectOptionType is an implementation of IOptionType for 'select' tags.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category 	Community Framework
 */
class SelectOptionType extends RadiobuttonsOptionType {
	/**
	 * @see wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		// get options
		$options = $this->parseEnableOptions($option);
		
		WCF::getTPL()->assign(array(
			'disableOptions' => $options['disableOptions'],
			'enableOptions' => $options['enableOptions'],
			'option' => $option,
			'selectOptions' => $option->parseSelectOptions(),
			'value' => $value
		));
		return WCF::getTPL()->fetch('selectOptionType');
	}
	
	/**
	 * @see wcf\system\option\ISearchableUserOption::getSearchFormElement()
	 */
	public function getSearchFormElement(Option $option, $value) {
		return $this->getFormElement($optionData, $value);
	}
	
	/**
	 * @todo	This is not really tested yet!
	 * @param	Option		$option
	 * @return	array
	 */
	protected function parseEnableOptions(Option $option) {
		$disableOptions = $enableOptions = '';
		
		if (!empty($option->enableOptions)) {
			$options = $option->parseMultipleEnableOptions();
			
			foreach ($options as $key => $optionData) {
				$tmp = explode(',', $optionData);
				
				foreach ($optionData as $item) {
					if ($item{0} == '!') {
						if (!empty($disableOptions)) $disableOptions .= ',';
						$disableOptions .= "{ value: '".$key."', option: '".StringUtil::substring($item, 1)."' }";
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
}
