<?php
namespace wcf\system\html\metacode\converter;

/**
 * Default interface for metacode converters.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Metacode\Converter
 * @since       3.0
 */
interface IMetacodeConverter {
	/**
	 * Converts a known metacode into the HTML representation normally used by the WYSIWYG
	 * editor. This process is designed to turn simple bbcodes into their HTML counterpart
	 * without forcing the bbcode to be evaluated each time.
	 * 
	 * The fragment must be inserted into your returned DOM element.
	 * 
	 * @param	\DOMDocumentFragment	$fragment	fragment containing all child nodes, must be appended to returned element
	 * @param	array			$attributes	list of attributes
	 * @return	\DOMElement		new DOM element
	 */
	public function convert(\DOMDocumentFragment $fragment, array $attributes);
	
	/**
	 * Validates attributes before any DOM modification occurs.
	 * 
	 * @param	array		$attributes	list of attributes
	 * @return	boolean		false if attributes did not match the converter's expectation
	 */
	public function validateAttributes(array $attributes);
}
