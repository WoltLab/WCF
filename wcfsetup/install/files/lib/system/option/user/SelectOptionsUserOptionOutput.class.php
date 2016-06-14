<?php
namespace wcf\system\option\user;
use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\system\WCF;
use wcf\util\OptionUtil;

/**
 * User option output implementation for the output of select options.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option\User
 */
class SelectOptionsUserOptionOutput implements IUserOptionOutput {
	/**
	 * @inheritDoc
	 */
	public function getOutput(User $user, UserOption $option, $value) {
		$result = self::getResult($option, $value);
		if ($result === null) {
			return '';
		}
		else if (is_array($result)) {
			$output = '';
			foreach ($result as $resultValue) {
				if (!empty($output)) $output .= "<br>\n";
				$output .= WCF::getLanguage()->get($resultValue);
			}
			
			return $output;
		}
		else {
			return WCF::getLanguage()->get($result);
		}
	}
	
	/**
	 * Returns the selected option value(s) for output.
	 * 
	 * @param	\wcf\data\user\option\UserOption		$option
	 * @param	string					$value
	 * @return	mixed
	 */
	protected static function getResult(UserOption $option, $value) {
		$options = OptionUtil::parseSelectOptions($option->selectOptions);
		
		// multiselect
		if (mb_strpos($value, "\n") !== false) {
			$values = explode("\n", $value);
			$result = [];
			foreach ($values as $value) {
				if (isset($options[$value])) {
					$result[] = $options[$value];
				}
			}
			
			return $result;
		}
		else {
			if (!empty($value) && isset($options[$value])) return $options[$value];
			return null;
		}
	}
}
