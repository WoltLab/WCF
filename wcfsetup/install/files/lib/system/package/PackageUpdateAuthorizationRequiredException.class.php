<?php
namespace wcf\system\package;
use wcf\acp\form\PackageUpdateAuthForm;

/**
 * A PackageUpdateAuthorizationRequiredException is thrown when a package update server requires a user authorization.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package
 * @category 	Community Framework
 */
class PackageUpdateAuthorizationRequiredException extends UserException {
	protected $packageUpdateServerID = 0;
	protected $url = '';
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
		return $this->response['header'];
	}
	
	/**
	 * Returns the response content.
	 *
	 * @return	string
	 */
	public function getResponseContent() {
		return $this->response['content'];
	}
}
?>
