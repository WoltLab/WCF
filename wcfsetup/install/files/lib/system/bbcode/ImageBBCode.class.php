<?php
namespace wcf\system\bbcode;
use wcf\util\exception\CryptoException;
use wcf\util\CryptoUtil;
use wcf\util\StringUtil;
use wcf\system\WCF;
use wcf\system\request\LinkHandler;

/**
 * Parses the [img] bbcode tag.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode
 * @category	Community Framework
 */
class ImageBBCode extends AbstractBBCode {
	/**
	 * @see	\wcf\system\bbcode\IBBCode::getParsedTag()
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		$src = '';
		if (isset($openingTag['attributes'][0])) {
			$src = $openingTag['attributes'][0];
		}
		
		if ($parser->getOutputType() == 'text/html') {
			$dataSource = '';
			if (MODULE_IMAGE_PROXY) {
				$dataSource = $src;
				$src = $this->getProxyLink($src);
			}
			
			$float = '';
			if (isset($openingTag['attributes'][1])) {
				$float = $openingTag['attributes'][1];
			}
			
			$style = '';
			if ($float == 'left' || $float == 'right') {
				$style = 'float: ' . $float . '; margin: ' . ($float == 'left' ? '0 15px 7px 0' : '0 0 7px 15px') . ';';
			}
			
			$width = 0;
			if (isset($openingTag['attributes'][2])) {
				$width = $openingTag['attributes'][2];
				
				$style .= 'width: ' . $width . 'px;';
			}
			
			return '<img src="'.$src.'" class="jsResizeImage" alt=""'.($style ? ' style="' . $style . '"' : '').($dataSource ? ' data-source="'.StringUtil::encodeJS($dataSource).'"' : '').' />';
		}
		else if ($parser->getOutputType() == 'text/simplified-html') {
			$src = StringUtil::decodeHTML($src);
			$path = parse_url($src, PHP_URL_PATH);
			if ($path !== false) {
				return StringUtil::encodeHTML(basename($path));
			}
			
			return '';
		}
	}
	
	/**
	 * Returns the link to the cached image (or the link to fetch the image
	 * using the image proxy).
	 * 
	 * @param	string		$link
	 * @return	string
	 */
	protected function getProxyLink($link) {
		try {
			$key = CryptoUtil::createSignedString($link);
			// does not need to be secure, just sufficiently "random"
			$fileName = sha1($key);
			
			$fileExtension = pathinfo($this->url, PATHINFO_EXTENSION);
			
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
