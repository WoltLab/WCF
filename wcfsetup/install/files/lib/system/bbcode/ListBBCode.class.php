<?php
namespace wcf\system\bbcode;
use wcf\util\StringUtil;

/**
 * Parses the [list] bbcode tag.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode
 */
class ListBBCode extends AbstractBBCode {
	/**
	 * @inheritDoc
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		if (mb_strpos($content, '[*]') !== false) {
			// get list elements
			$listElements = preg_split('/\[\*\]/', StringUtil::trim($content), -1, PREG_SPLIT_NO_EMPTY);
			
			// remove empty elements
			foreach ($listElements as $key => $val) {
				$listElements[$key] = StringUtil::trim($val);
				if (empty($listElements[$key]) || $listElements[$key] == '<br />' || $listElements[$key] == '<br>') {
					unset($listElements[$key]);
				}
			}
			
			if (!empty($listElements)) {
				// get list style type
				$listType = 'disc';
				if (isset($openingTag['attributes'][0])) $listType = $openingTag['attributes'][0];
				$listType = strtolower($listType);
				
				// replace old types
				if ($listType == '1') $listType = 'decimal';
				if ($listType == 'a') $listType = 'lower-latin';
				
				if ($parser->getOutputType() == 'text/html') {
					// build list html
					$listHTML = 'ol';
					if ($listType == 'none' || $listType == 'circle' || $listType == 'square' || $listType == 'disc') {
						$listHTML = 'ul';
					}
					
					return '<'.$listHTML.' style="list-style-type: '.$listType.'" class="nativeList"><li>'.implode('</li><li>', $listElements).'</li></'.$listHTML.'>';
				}
				else if ($parser->getOutputType() == 'text/simplified-html') {
					$result = '';
					
					$i = 1;
					foreach ($listElements as $listElement) {
						switch ($listType) {
							case 'decimal':
								$result .= $i.'. ';
								break;
							default:
								$result .= '- ';
						}
						
						$result .= $listElement."\n";
						$i++;
					}
					
					return $result;
				}
			}
		}
		
		// no valid list
		// return bbcode as text
		return $openingTag['source'].$content.$closingTag['source'];
	}
}
