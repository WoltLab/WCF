<?php
namespace wcf\system\html\output\node;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
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
		if (!MODULE_IMAGE_PROXY) {
			return;
		}
		
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$src = $element->getAttribute('src');
			if ($src) {
				$element->setAttribute('src', $this->getProxyLink($src));
			}
		}
	}
	
	/**
	 * Returns the link to the cached image (or the link to fetch the image
	 * using the image proxy).
	 *
	 * @param	string		$link
	 * @return	string
	 * @since	3.0
	 */
	protected function getProxyLink($link) {
		try {
			$key = CryptoUtil::createSignedString($link);
			// does not need to be secure, just sufficiently "random"
			$fileName = sha1($key);
			
			$fileExtension = pathinfo($link, PATHINFO_EXTENSION);
			
			$path = 'images/proxy/'.substr($fileName, 0, 2).'/'.$fileName.($fileExtension ? '.'.$fileExtension : '');
			
			$fileLocation = WCF_DIR.$path;
			if (file_exists($fileLocation)) {
				return WCF::getPath().$path;
			}
			
			return LinkHandler::getInstance()->getLink('ImageProxy', [
				'key' => $key
			]);
		}
		catch (CryptoException $e) {
			return $link;
		}
	}
}
