<?php
namespace wcf\system\html\metacode\converter;
use wcf\util\StringUtil;

/**
 * Converts url bbcode into `<a>`.
 * 
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Metacode\Converter
 * @since       3.0
 */
class UrlMetacodeConverter extends AbstractMetacodeConverter {
	/**
	 * list of allowed schemas as defined by HTMLPurifier
	 * @var string[] 
	 */
	public static $allowedSchemes = ['http', 'https', 'mailto', 'ftp', 'nntp', 'news', 'tel', 'steam', 'ts3server'];
	
	/**
	 * @inheritDoc
	 */
	public function convert(\DOMDocumentFragment $fragment, array $attributes) {
		$element = $fragment->ownerDocument->createElement('a');
		
		$href = (!empty($attributes[0])) ? $attributes[0] : '';
		if (empty($href)) {
			$href = $fragment->textContent;
		}
		
		$href = StringUtil::decodeHTML($href);
		if (mb_strpos($href, '//') === 0) {
			// dynamic protocol, treat as https
			$href = "https:{$href}";
		}
		else if (preg_match('~^(?P<schema>[a-z0-9]+)://~', $href, $match)) {
			if (!in_array($match['schema'], self::$allowedSchemes)) {
				// invalid schema, replace it with `http`
				$href = 'http' . mb_substr($href, strlen($match['schema']));
			}
		}
		else if (mb_strpos($href, 'index.php') === false) {
			// unless it's a relative `index.php` link, assume it is missing the protocol
			$href = "http://{$href}";
		}
		
		// check if the link is empty, use the href value instead
		$useHrefAsValue = false;
		if ($fragment->childNodes->length === 0) {
			$useHrefAsValue = true;
		}
		else if ($fragment->childNodes->length === 1) {
			$node = $fragment->childNodes->item(0);
			if ($node->nodeType === XML_TEXT_NODE && StringUtil::trim($node->textContent) === '') {
				$useHrefAsValue = true;
			}
		}
		
		if ($useHrefAsValue) {
			if ($fragment->childNodes->length === 1) {
				$fragment->removeChild($fragment->childNodes->item(0));
			}
			
			$fragment->appendChild($fragment->ownerDocument->createTextNode($href));
		}
		
		$element->setAttribute('href', $href);
		$element->appendChild($fragment);
		
		return $element;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateAttributes(array $attributes) {
		if (count($attributes) > 1) {
			return false;
		}
		
		return true;
	}
}
