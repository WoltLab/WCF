<?php
namespace wcf\system\package;
use wcf\acp\form\PackageUpdateAuthForm;
use wcf\system\exception\UserException;

/**
 * A PackageUpdateAuthorizationRequiredException is thrown when a package update
 * server requires a user authorization.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package
 * @category	Community Framework
 */
class PackageUpdateAuthorizationRequiredException extends UserException {
	/**
	 * id of the package update server that requires authorization
	 * @var	integer
	 */
	protected $packageUpdateServerID = 0;
	
	/**
	 * url of the requested package update
	 * @var	string
	 */
	protected $url = '';
	
	/**
	 * package update sever response data
	 * @var	array
	 */
	protected $response = array();
	
	/**
	 * Creates a new PackageUpdateAuthorizationRequiredException object.
	 * 
	 * @param	integer		$packageUpdateServerID
	 * @param	string		$url
	 * @param	array		$response
	 */
	public function __construct($packageUpdateServerID, $url, array $response) {
		$this->packageUpdateServerID = $packageUpdateServerID;
		$this->url = $url;
		$this->response = $response;
	}
	
	/**
	 * Shows the package update authentification form.
	 */
	public function show() {
		new PackageUpdateAuthForm($this);
		exit;
	}
	
	/**
	 * Returns the package update server id.
	 * 
	 * @return	integer
	 */
	public function getPackageUpdateServerID() {
		return $this->packageUpdateServerID;
	}
	
	/**
	 * Returns the server url.
	 * 
	 * @return	string
	 */
	public function getURL() {
		return $this->url;
	}
	
	/**
	 * Returns the response header.
	 * 
	 * @return	string
	 */
	public function getResponseHeader() {
		return $this->response['headers'];
	}
	
	/**
	 * Returns the response content.
	 * 
	 * @return	string
	 */
	public function getResponseContent() {
		return $this->response['body'];
	}
}
