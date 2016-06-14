<?php
namespace wcf\action;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\SystemException;
use wcf\util\exception\CryptoException;
use wcf\util\CryptoUtil;
use wcf\util\FileUtil;
use wcf\util\HTTPRequest;
use wcf\util\StringUtil;

/**
 * Proxies requests for embedded images.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Action
 * @since	3.0
 */
class ImageProxyAction extends AbstractAction {
	/**
	 * The image key created by CryptoUtil::createSignedString()
	 * @var	string
	 */
	public $key = '';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['key'])) $this->key = StringUtil::trim($_REQUEST['key']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		try {
			$url = CryptoUtil::getValueFromSignedString($this->key);
			if ($url === null) throw new IllegalLinkException();
			
			$fileName = sha1($this->key);
			
			// prepare path
			$fileExtension = pathinfo($url, PATHINFO_EXTENSION);
			$fileLocation = WCF_DIR.'images/proxy/'.substr($fileName, 0, 2).'/'.$fileName.($fileExtension ? '.'.$fileExtension : '');
			$imageData = getimagesize($fileLocation);
			$dir = dirname($fileLocation);
			if (!@file_exists($dir)) {
				FileUtil::makePath($dir);
			}
			
			// download image
			if (!file_exists($fileLocation)) {
				$request = new HTTPRequest($url);
				$request->execute();
				$image = $request->getReply()['body'];
				
				// check if image is linked
				// TODO: handle SVGs
				$imageData = getimagesizefromstring($image);
				if (!$imageData) {
					throw new IllegalLinkException();
				}
				
				file_put_contents($fileLocation, $image);
				
				// update mtime for correct expiration calculation
				@touch($fileLocation);
			}
			$this->executed();
			
			@header('Content-Type: '.$imageData['mime']);
			@readfile($fileLocation);
			exit;
		}
		catch (SystemException $e) {
			throw new IllegalLinkException();
		}
		catch (CryptoException $e) {
			throw new IllegalLinkException();
		}
	}
}
