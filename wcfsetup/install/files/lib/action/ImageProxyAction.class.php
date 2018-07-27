<?php
namespace wcf\action;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\exception\CryptoException;
use wcf\util\exception\HTTPException;
use wcf\util\CryptoUtil;
use wcf\util\FileUtil;
use wcf\util\HeaderUtil;
use wcf\util\HTTPRequest;
use wcf\util\StringUtil;

/**
 * Proxies requests for embedded images.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Action
 * @since	3.0
 */
class ImageProxyAction extends AbstractAction {
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_IMAGE_PROXY'];
	
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
		
		if (isset($_SERVER['HTTP_VIA']) && strpos($_SERVER['HTTP_VIA'], 'wsc') !== false) throw new IllegalLinkException();
		
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
			$fileLocation = null;
			foreach (['png','jpg','gif'] as $extension) {
				if (is_file($dir.'/'.$fileName.'.'.$extension)) {
					$fileLocation = $dir.'/'.$fileName.'.'.$extension;
					break;
				}
			}
			
			if ($fileLocation === null) {
				try {
					// download image
					try {
						$request = new HTTPRequest($url, [
							'maxLength' => 10 * (1 << 20) // download at most 10 MiB
						]);
						$request->addHeader('Via', '1.1 wsc');
						$request->addHeader('Accept', 'image/*');
						$request->execute();
					}
					catch (\Exception $e) {
						$chain = $e;
						do {
							if ($chain instanceof HTTPException) {
								throw new \DomainException();
							}
							// TODO: This is the Exception thrown by RemoteFile.
							//       Update if RemoteFile switches to a proper subclass in the future.
							if (strpos($chain->getMessage(), 'Can not connect to') === 0) {
								throw new \DomainException();
							}
						}
						while ($chain = $chain->getPrevious());
						
						throw $e;
					}
					$image = $request->getReply()['body'];
					
					// check file type
					$imageData = @getimagesizefromstring($image);
					if (!$imageData) throw new \DomainException();
					
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
							throw new \DomainException();
					}
				}
				catch (\DomainException $e) {
					// save a dummy image in case the server sent us junk, otherwise we might try to download the file over and over and over again.
					// taken from the public domain gif at https://commons.wikimedia.org/wiki/File%3aBlank.gif
					$image = "\x47\x49\x46\x38\x39\x61\x01\x00\x01\x00\x80\x00\x00\xFF\xFF\xFF\x00\x00\x00\x21\xF9\x04\x00\x00\x00\x00\x00\x2C\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02\x44\x01\x00\x3B";
					$extension = 'gif';
				}
				
				$fileLocation = $dir.'/'.$fileName.'.'.$extension;
				
				file_put_contents($fileLocation, $image);
				
				// update mtime for correct expiration calculation
				@touch($fileLocation);
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
