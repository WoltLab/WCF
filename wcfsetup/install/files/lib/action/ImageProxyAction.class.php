<?php
namespace wcf\action;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\SystemException;
use wcf\util\FileUtil;
use wcf\util\HTTPRequest;
use wcf\util\StringUtil;

/**
 * Caches external images locally.
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
	 * url-encoded image url
	 * @var	string
	 */
	public $url = '';
	
	/**
	 * @see	\wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['url'])) $this->url = StringUtil::trim($_REQUEST['url']);
		if (isset($_REQUEST['hash'])) $this->hash = StringUtil::trim($_REQUEST['hash']);
	}
	
	/**
	 * @see	\wcf\action\IAction::execute()
	 */
	public function execute() {
		parent::execute();
		
		$url = urldecode($this->url);
		$hash = sha1(IMAGE_PROXY_SECRET.$url);
		if ($this->hash != $hash) {
			throw new IllegalLinkException();
		}
		
		try {
			$request = new HTTPRequest($url);
			$request->execute();
			$reply = $request->getReply();
			
			$fileExtension = '';
			if (($position = mb_strrpos($url, '.')) !== false) {
				$fileExtension = mb_strtolower(mb_substr($url, $position + 1));
			}
			
			// check if requested content is image
			if (!isset($reply['headers']['Content-Type']) || !StringUtil::startsWith($reply['headers']['Content-Type'], 'image/')) {
				throw new IllegalLinkException();
			}
			
			// save image
			$fileLocation = WCF_DIR.'images/proxy/'.substr($hash, 0, 2).'/'.$hash.($fileExtension ? '.'.$fileExtension : '');
			$dir = dirname($fileLocation);
			if (!@file_exists($dir)) {
				FileUtil::makePath($dir, 0777);
			}
			file_put_contents($fileLocation, $reply['body']);
			
			// update mtime for correct expiration calculation
			@touch($fileLocation);
			
			$this->executed();
			
			@header('Content-Type: '.$reply['headers']['Content-Type']);
			@readfile($fileLocation);
			exit;
		}
		catch (SystemException $e) {
			throw new IllegalLinkException();
		}
	}
}
