<?php
namespace wcf\action;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\exception\CryptoException;
use wcf\util\CryptoUtil;
use wcf\util\FileUtil;
use wcf\util\HeaderUtil;
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
			$dir = WCF_DIR.'images/proxy/'.substr($fileName, 0, 2);
			
			// ensure that the directory exists
			if (!file_exists($dir)) {
				FileUtil::makePath($dir);
			}
			
			// check whether we already downloaded the image
			$files = glob($dir.'/'.$fileName.'.{png,gif,jpg}', GLOB_BRACE | GLOB_NOSORT);
			if ($files === false) throw new IllegalLinkException();
			
			if (empty($files)) {
				// download image
				try {
					$request = new HTTPRequest($url);
					$request->execute();
				}
				catch (SystemException $e) {
					throw new IllegalLinkException();
				}
				$image = $request->getReply()['body'];
				
				// check file type
				$imageData = getimagesizefromstring($image);
				if (!$imageData) throw new IllegalLinkException();
				
				switch ($imageData[2]) {
					case IMAGETYPE_PNG:
						$extension = 'png';
					break;
					case IMAGETYPE_GIF:
						$extension = 'gif';
					break;
					case IMAGETYPE_JPEG:
						$extension = 'jpg';
					break;
					default:
						throw new IllegalLinkException();
				}
				
				$fileLocation = $dir.'/'.$fileName.'.'.$extension;
				
				file_put_contents($fileLocation, $image);
				
				// update mtime for correct expiration calculation
				@touch($fileLocation);
			}
			else {
				$fileLocation = $files[0];
			}
			
			$path = FileUtil::getRelativePath(WCF_DIR, dirname($fileLocation)).basename($fileLocation);
			
			$this->executed();
			
			HeaderUtil::redirect(WCF::getPath().$path, true, false);
			exit;
		}
		catch (SystemException $e) {
			\wcf\functions\exception\logThrowable($e);
			throw new IllegalLinkException();
		}
		catch (CryptoException $e) {
			\wcf\functions\exception\logThrowable($e);
			throw new IllegalLinkException();
		}
	}
}
