<?php
namespace wcf\action;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\SystemException;
use wcf\util\FileUtil;
use wcf\util\HTTPRequest;
use wcf\util\PasswordUtil;
use wcf\util\StringUtil;

/**
 * Proxies requests for embedded images.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	action
 * @category	Community Framework
 */
class ImageProxyAction extends AbstractAction {
	/**
	 * hashed image proxy secret and image url
	 * @var	string
	 */
	public $hash = '';
	
	/**
	 * image url
	 * @var	string
	 */
	public $url = '';
	
	/**
	 * @see	\wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['url'])) $this->url = rawurldecode(StringUtil::trim($_REQUEST['url']));
		if (isset($_REQUEST['hash'])) $this->hash = StringUtil::trim($_REQUEST['hash']);
	}
	
	/**
	 * @see	\wcf\action\IAction::execute()
	 */
	public function execute() {
		parent::execute();
		
		$hash = sha1(IMAGE_PROXY_SECRET.$this->url);
		if (!PasswordUtil::secureCompare($this->hash, $hash)) {
			throw new IllegalLinkException();
		}
		
		try {
			$request = new HTTPRequest($this->url);
			$request->execute();
			$image = $request->getReply()['body'];
			
			// check if image is linked
			// TODO: handle SVGs
			$imageData = getimagesizefromstring($image);
			if (!$imageData) {
				throw new IllegalLinkException();
			}
			
			// save image
			$fileExtension = pathinfo($this->url, PATHINFO_EXTENSION);
			$fileLocation = WCF_DIR.'images/proxy/'.substr($hash, 0, 2).'/'.$hash.($fileExtension ? '.'.$fileExtension : '');
			$dir = dirname($fileLocation);
			if (!@file_exists($dir)) {
				FileUtil::makePath($dir, 0777);
			}
			file_put_contents($fileLocation, $image);
			
			// update mtime for correct expiration calculation
			@touch($fileLocation);
			
			$this->executed();
			
			@header('Content-Type: '.$imageData['mime']);
			@readfile($fileLocation);
			exit;
		}
		catch (SystemException $e) {
			throw new IllegalLinkException();
		}
	}
}
