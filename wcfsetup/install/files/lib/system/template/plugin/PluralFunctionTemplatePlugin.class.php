<?php
namespace wcf\system\template\plugin;
use wcf\system\exception\SystemException;
use wcf\system\language\I18nPlural;
use wcf\system\template\TemplateEngine;
use wcf\util\StringUtil;

/**
 * Template function plugin which generate plural phrases.
 *
 * Languages vary in how they handle plurals of nouns or unit expressions.
 * Some languages have two forms, like English; some languages have only a
 * single form; and some languages have multiple forms.
 * 
 * Supported parameters:
 * value (number|array|Countable - required)
 * other (string - required)
 * zero (string), one (string), two (string), few (string), many (string)
 * 
 * Usage:
 *      {plural value=$number zero='0' one='1' two='2' few='few' many='many' other='#'}
 *      There {plural value=$worlds one='is one world' other='are # worlds'}
 *      Updated {plural value=$minutes 0='just now' 1='one minute ago' other='# minutes ago'}
 *
 * @author	Marcel Werk
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 * @since	5.3
 */
class PluralFunctionTemplatePlugin implements IFunctionTemplatePlugin {
	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		if (!isset($tagArgs['value'])) {
			throw new SystemException("Missing attribute 'value'");
		}
		if (!isset($tagArgs['other'])) {
			throw new SystemException("Missing attribute 'other'");
		}
		
		$value = $tagArgs['value'];
		if (is_countable($value)) {
			$value = count($value);
		}
		
		// handle numeric attributes
		foreach ($tagArgs as $key => $_value) {
			if (is_numeric($key)) {
				if ($key == $value) return $_value;
			}
		}
		
		$category = I18nPlural::getCategory($value);
		if (!isset($tagArgs[$category])) {
			$category = I18nPlural::PLURAL_OTHER;
		}
		
		$string = $tagArgs[$category];
		if (strpos($string, '#') !== false) {
			return str_replace('#', StringUtil::formatNumeric($value), $string);
		}
		
		return $string;
	}
}
