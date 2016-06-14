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
 * @copyright	2001-2016 WoltLab GmbH
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
	 * @var	\wcf\util\HTTPRequest
	 */
	protected $request = null;
	
	/**
	 * package update server object
	 * @var	\wcf\data\package\update\server\PackageUpdateServer
	 */
	protected $updateServer = null;
	
	/**
	 * Creates a new PackageUpdateUnauthorizedException object.
	 * 
	 * @param	\wcf\util\HTTPRequest					$request
	 * @param	\wcf\data\package\update\server\PackageUpdateServer	$updateServer
	 * @param	array							$packageUpdateVersion
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
		WCF::getTPL()->assign([
			'packageUpdateVersion' => $this->packageUpdateVersion,
			'request' => $this->request,
			'updateServer' => $this->updateServer
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
	 * @return	\wcf\util\HTTPRequest
	 */
	public function getRequest() {
		return $this->request;
	}
	
	/**
	 * Returns package update server object.
	 * 
	 * @return	\wcf\data\package\update\server\PackageUpdateServer
	 */
	public function getUpdateServer() {
		return $this->updateServer;
	}
}
