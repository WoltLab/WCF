<?php
namespace wcf\data\custom\option;
use wcf\data\ITitledObject;
use wcf\data\language\Language;
use wcf\data\option\Option;
use wcf\system\bbcode\MessageParser;
use wcf\system\bbcode\SimpleMessageParser;
use wcf\system\exception\NotImplementedException;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\OptionUtil;
use wcf\util\StringUtil;

/**
 * Default implementation for custom options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Custom\Option
 * @since	3.1
 * 
 * @property-read	integer		$optionID		unique id of the option
 * @property-read	string		$optionTitle		title of the option or name of language item which contains the title
 * @property-read	string		$optionDescription	description of the option or name of language item which contains the description
 * @property-read	string		$optionType		type of the option which determines its input and output
 * @property-read	string		$defaultValue		default value of the option
 * @property-read	string		$validationPattern	regular expression used to validate the value of the option
 * @property-read	string		$selectOptions		possible values of the option separated by newlines
 * @property-read	integer		$required		is `1` if the option has to be filled out, otherwise `0`
 * @property-read	integer		$showOrder		position of the option in relation to the other options
 * @property-read	integer		$isDisabled		is `1` if the option is disabled, otherwise `0`
 * @property-read	integer		$originIsSystem		is `1` if the option has been delivered by a package, otherwise `0` (i.e. the option has been created in the ACP)
 */
abstract class CustomOption extends Option implements ITitledObject {
	/**
	 * option value
	 * @var	string
	 */
	protected $optionValue = '';
	
	/**
	 * @inheritDoc
	 */
	public function __get($name) {
		// Some options support empty values, such as "select", but the code checks for the
		// property `allowEmptyValue`, which is the inverse value of `required`.
		if ($name === 'allowEmptyValue') {
			return !$this->required;
		}
		
		return parent::__get($name);
	}
	
	/**
	 * @inheritDoc
	 * @since	5.2
	 */
	public function getTitle() {
		return WCF::getLanguage()->get($this->optionTitle);
	}

	/**
	 * Returns the option description in the active user's language.
	 * 
	 * @return	string
	 * @since	5.2
	 */
	public function getDescription() {
		return WCF::getLanguage()->get($this->optionDescription);
	}
	
	/**
	 * Returns true if the option is visible
	 *
	 * @return	boolean
	 */
	public function isVisible() {
		return !$this->isDisabled;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getDatabaseTableAlias() {
		throw new NotImplementedException();
	}
	
	/**
	 * Returns the value of this option.
	 * 
	 * @return	string
	 */
	public function getOptionValue() {
		return $this->optionValue;
	}
	
	/**
	 * Sets the value of this option.
	 *
	 * @param	string		$value
	 */
	public function setOptionValue($value) {
		$this->optionValue = $value;
	}
	
	/**
	 * Attempts to return the localized option name.
	 * 
	 * @param       Language        $language
	 * @return      string
	 */
	public function getLocalizedName(Language $language) {
		if (preg_match('~^wcf\.contact\.option\d+$~', $this->optionTitle)) {
			return $language->get($this->optionTitle);
		}
		
		return $this->optionTitle;
	}
	
	/**
	 * Returns the formatted value of this option.
	 * 
	 * @param       boolean         $forcePlaintext
	 * @return	string
	 */
	public function getFormattedOptionValue($forcePlaintext = false) {
		switch ($this->optionType) {
			case 'boolean':
				return WCF::getLanguage()->get('wcf.acp.customOption.optionType.boolean.'.($this->optionValue ? 'yes' : 'no'));
				
			case 'date':
				$year = $month = $day = 0;
				$optionValue = explode('-', $this->optionValue);
				if (isset($optionValue[0])) $year = intval($optionValue[0]);
				if (isset($optionValue[1])) $month = intval($optionValue[1]);
				if (isset($optionValue[2])) $day = intval($optionValue[2]);
				return DateUtil::format(DateUtil::getDateTimeByTimestamp(gmmktime(12, 1, 1, $month, $day, $year)), DateUtil::DATE_FORMAT);
			
			case 'float':
				return StringUtil::formatDouble(intval($this->optionValue));
				
			case 'integer':
				return StringUtil::formatInteger(intval($this->optionValue));
				
			case 'radioButton':
			case 'select':
				$selectOptions = OptionUtil::parseSelectOptions($this->selectOptions);
				if (isset($selectOptions[$this->optionValue])) return WCF::getLanguage()->get(($forcePlaintext ? $selectOptions[$this->optionValue] : StringUtil::encodeHTML($selectOptions[$this->optionValue])));
				return '';
				
			case 'multiSelect':
			case 'checkboxes':
				$selectOptions = OptionUtil::parseSelectOptions($this->selectOptions);
				$values = explode("\n", $this->optionValue);
				$result = '';
				foreach ($values as $value) {
					if (isset($selectOptions[$value])) {
						if (!empty($result)) {
							if ($forcePlaintext) $result .= "\n";
							else $result .= "<br>";
						}
						$result .= WCF::getLanguage()->get(($forcePlaintext ? $selectOptions[$value] : StringUtil::encodeHTML($selectOptions[$value])));
					}
				}
				return $result;
			
			/** @noinspection PhpMissingBreakStatementInspection */
			case 'textarea':
				if (!$forcePlaintext) return SimpleMessageParser::getInstance()->parse($this->optionValue);
				// fallthrough
			
			/** @noinspection PhpMissingBreakStatementInspection */
			case 'message':
				if (!$forcePlaintext) return MessageParser::getInstance()->parse($this->optionValue);
				// fallthrough
			
			/** @noinspection PhpMissingBreakStatementInspection */
			case 'URL':
				if (!$forcePlaintext) return StringUtil::getAnchorTag($this->optionValue, '', true, true);
				// fallthrough
				
			default:
				if (!$forcePlaintext) return StringUtil::encodeHTML($this->optionValue);
				return $this->optionValue;
		}
	}
	
	/**
	 * Returns true if this option can be deleted, defaults to false for
	 * options created through the package system.
	 * 
	 * @return      boolean
	 */
	public function canDelete() {
		return !$this->originIsSystem;
	}
	
	/**
	 * Returns true if this option represents a message-type value.
	 * 
	 * @return      boolean
	 */
	public function isMessage() {
		return ($this->optionType === 'textarea' || $this->optionType === 'message');
	}
}
