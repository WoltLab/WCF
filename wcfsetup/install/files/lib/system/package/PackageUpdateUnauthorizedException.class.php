<?php
namespace wcf\system\package;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\system\exception\UserException;
use wcf\system\WCF;
use wcf\util\HTTPRequest;

/**
 * Credentials for update server are either missing or invalid.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package
 */
class PackageUpdateUnauthorizedException extends UserException {
	/**
	 * package update version
	 * @var	array
	 */
	protected $packageUpdateVersion = [];
	
	/**
	 * HTTP request object
	 * @var	HTTPRequest
	 */
	protected $request = null;
	
	/**
	 * package update server object
	 * @var	PackageUpdateServer
	 */
	protected $updateServer = null;
	
	/**
	 * Creates a new PackageUpdateUnauthorizedException object.
	 * 
	 * @param	HTTPRequest		$request
	 * @param	PackageUpdateServer	$updateServer
	 * @param	array			$packageUpdateVersion
	 */
	public function __construct(HTTPRequest $request, PackageUpdateServer $updateServer, array $packageUpdateVersion = []) {
		$this->request = $request;
		$this->updateServer = $updateServer;
		$this->packageUpdateVersion = $packageUpdateVersion;
	}
	
	/**
	 * Returns the rendered template.
	 * 
	 * @return	string
	 */
	public function getRenderedTemplate() {
		$serverReply = $this->request->getReply();
		
		WCF::getTPL()->assign([
			'authInsufficient' => (isset($serverReply['httpHeaders']['wcf-update-server-auth'][0]) && $serverReply['httpHeaders']['wcf-update-server-auth'][0] === 'unauthorized'),
			'packageUpdateVersion' => $this->packageUpdateVersion,
			'request' => $this->request,
			'updateServer' => $this->updateServer,
			'serverAuthData' => $this->updateServer->getAuthData(),
			'serverReply' => $serverReply
		]);
		
		return WCF::getTPL()->fetch('packageUpdateUnauthorized');
	}
	
	/**
	 * Returns package update version.
	 * 
	 * @return	array
	 */
	public function getPackageUpdateVersion() {
		return $this->packageUpdateVersion;
	}
	
	/**
	 * Returns the HTTP request object.
	 * 
	 * @return	HTTPRequest
	 */
	public function getRequest() {
		return $this->request;
	}
	
	/**
	 * Returns package update server object.
	 * 
	 * @return	PackageUpdateServer
	 */
	public function getUpdateServer() {
		return $this->updateServer;
	}
}
