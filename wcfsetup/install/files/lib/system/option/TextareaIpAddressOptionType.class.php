<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\UserUtil;

/**
 * TextareaIpAddressOptionType is an implementation of IOptionType for 'textarea'
 * tags with IPv4/IPv6 support.
 * IP addresses will be converted into IPv6 upon saving but will be displayed as
 * IPv4 whenever applicable.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class TextareaIpAddressOptionType extends TextOptionType {
	/**
	 * @see	wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		if (!empty($value)) {
			$ips = explode("\n", $value);
			foreach ($ips as &$ip) {
				$ip = UserUtil::convertIPv6To4($ip);
			}
			unset($ip);
			
			$value = implode("\n", $ips);
		}
		
		WCF::getTPL()->assign(array(
			'option' => $option,
			'value' => $value
		));
		return WCF::getTPL()->fetch('textareaOptionType');
	}
	
	/**
	 * @see	wcf\system\option\IOptionType::validate()
	 */
	public function validate(Option $option, $newValue) {
		if (!empty($newValue)) {
			$ips = explode("\n", $newValue);
			foreach ($ips as $ip) {
				$ip = trim($ip);
				
				$ip = UserUtil::convertIPv6To4($ip);
				if (empty($ip)) {
					throw new UserInputException($option->optionName, 'validationFailed');
				}
			}
		}
	}
	
	/**
	 * @see	wcf\system\option\IOptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		if (!empty($newValue)) {
			$ips = explode("\n", $newValue);
			foreach ($ips as &$ip) {
				$ip = trim($ip);
				$ip = UserUtil::convertIPv4To6($ip);
			}
			unset($ip);
			
			$newValue = implode("\n", $ips);
		}
		
		return $newValue;
	}
}
