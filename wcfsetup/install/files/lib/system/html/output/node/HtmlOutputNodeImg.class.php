<?php
namespace wcf\system\html\output\node;
use wcf\data\smiley\Smiley;
use wcf\data\smiley\SmileyCache;
use wcf\system\application\ApplicationHandler;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\request\LinkHandler;
use wcf\util\exception\CryptoException;
use wcf\util\CryptoUtil;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;

/**
 * Processes images.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2017 WoltLab GmbH
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
					
					if ($smiley->getHeight()) $element->setAttribute('height', $smiley->getHeight());
					else $element->removeAttribute('height');
					
					if ($smiley->smileyPath2x) $element->setAttribute('srcset', $smiley->getURL2x() . ' 2x');
					else $element->removeAttribute('srcset');
				}
			}
			else {
				$src = $element->getAttribute('src');
				if (!$src) {
					DOMUtil::removeNode($element);
					continue;
				}
				
				$class = $element->getAttribute('class');
				if ($class) $class .= ' ';
				$class .= 'jsResizeImage';
				$element->setAttribute('class', $class);
				
				if (MODULE_IMAGE_PROXY) {
					$urlComponents = parse_url($src);
					if ($urlComponents === false) {
						// not a valid URL, discard it
						DOMUtil::removeNode($element);
						continue;
					}
					
					if (empty($urlComponents['host'])) {
						// relative URL, ignore it
						continue;
					}
					
					$element->setAttribute('data-valid', 'true');
					
					if (!empty($urlComponents['path']) && preg_match('~\.svg~', basename($urlComponents['path']))) {
						// we can't proxy SVG, ignore it
						continue;
					}
					
					$element->setAttribute('src', $this->getProxyLink($src));
					
					$srcset = $element->getAttribute('srcset');
					if ($srcset) {
						// simplified regex to check if it appears to be a valid list of sources
						if (!preg_match('~^[^\s]+\s+[0-9\.]+[wx](,\s*[^\s]+\s+[0-9\.]+[wx])*~', $srcset)) {
							$element->removeAttribute('srcset');
							continue;
						}
						
						$sources = explode(',', $srcset);
						$srcset = '';
						foreach ($sources as $source) {
							$tmp = preg_split('~\s+~', StringUtil::trim($source));
							if (!empty($srcset)) $srcset .= ', ';
							$srcset .= $this->getProxyLink($tmp[0]) . ' ' . $tmp[1];
						}
						
						$element->setAttribute('srcset', $srcset);
					}
				}
				else if (!IMAGE_ALLOW_EXTERNAL_SOURCE && !$this->isAllowedOrigin($src)) {
					$element->parentNode->insertBefore($element->ownerDocument->createTextNode('[IMG:'), $element);
					
					$link = $element->ownerDocument->createElement('a');
					$link->setAttribute('href', $src);
					$link->textContent = $src;
					$element->parentNode->insertBefore($link, $element);
					
					$element->parentNode->insertBefore($element->ownerDocument->createTextNode(']'), $element);
					
					$element->parentNode->removeChild($element);
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
	
	protected function isAllowedOrigin($src) {
		static $ownDomains;
		if ($ownDomains === null) {
			$ownDomains = array();
			foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
				if (!in_array($application->domainName, $ownDomains)) {
					$ownDomains[] = $application->domainName;
				}
			}
		}
		
		$host = @parse_url($src, PHP_URL_HOST);
		return $host !== false && ($host === null || in_array($host, $ownDomains));
	}
}
