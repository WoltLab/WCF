<?php
namespace wcf\system\html\output\node;
use wcf\data\smiley\Smiley;
use wcf\data\smiley\SmileyCache;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\request\LinkHandler;
use wcf\util\exception\CryptoException;
use wcf\util\CryptoUtil;

/**
 * Processes images.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Output\Node
 * @since       3.0
 */
class HtmlOutputNodeImg extends AbstractHtmlOutputNode {
	/**
	 * @inheritDoc
	 */
	protected $tagName = 'img';
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$class = $element->getAttribute('class');
			if (preg_match('~\bsmiley\b~', $class)) {
				$code = $element->getAttribute('alt');
				
				/** @var Smiley $smiley */
				$smiley = SmileyCache::getInstance()->getSmileyByCode($code);
				if ($smiley === null) {
					// output as raw code instead
					$element->parentNode->insertBefore($element->ownerDocument->createTextNode($code), $element);
					$element->parentNode->removeChild($element);
				}
				else {
					// enforce database values for src, srcset and style
					$element->setAttribute('src', $smiley->getURL());
					
					if ($smiley->getHeight()) $element->setAttribute('style', 'height: ' . $smiley->getHeight() . 'px');
					else $element->removeAttribute('style');
					
					if ($smiley->smileyPath2x) $element->setAttribute('srcset', $smiley->getURL2x() . ' 2x');
					else $element->removeAttribute('srcset');
				}
			}
			else if (MODULE_IMAGE_PROXY) {
				$src = $element->getAttribute('src');
				if ($src) {
					$element->setAttribute('src', $this->getProxyLink($src));
				}
			}
		}
	}
	
	/**
	 * Returns the link to fetch the image using the image proxy.
	 *
	 * @param	string		$link
	 * @return	string
	 * @since	3.0
	 */
	protected function getProxyLink($link) {
		try {
			$key = CryptoUtil::createSignedString($link);
			
			return LinkHandler::getInstance()->getLink('ImageProxy', [
				'key' => $key
			]);
		}
		catch (CryptoException $e) {
			return $link;
		}
	}
}
