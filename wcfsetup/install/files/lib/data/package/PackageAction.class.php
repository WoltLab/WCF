<?php
namespace wcf\data\package;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\HTTPUnauthorizedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\HTTPRequest;
use wcf\util\JSON;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;

/**
 * Executes package-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package
 * @category	Community Framework
 */
class PackageAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\package\PackageEditor';
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsCreate
	 */
	protected $permissionsCreate = array('admin.system.package.canInstallPackage');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.system.package.canUninstallPackage');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.system.package.canUpdatePackage');
	
	public function validateSearchForPurchasedItems() {
		// TODO: validate permissions
		
		$this->readString('password', true);
		$this->readString('username', true);
		
		if (empty($this->parameters['wcfMajorReleases']) || !is_array($this->parameters['wcfMajorReleases'])) {
			throw new UserInputException('wcfMajorReleases');
		}
		
		if (empty($this->parameters['username'])) {
			// check if user has already provided credentials
			$sql = "SELECT	loginUsername, loginPassword
				FROM	wcf".WCF_N."_package_update_server
				WHERE	serverURL = ?";
			$statement = WCF::getDB()->prepareStatement($sql, 1);
			$statement->execute(array('http://store.woltlab.com/typhoon/'));
			$row = $statement->fetchArray();
			if (!empty($row['loginUsername']) && !empty($row['loginPassword'])) {
				$this->parameters['password'] = $row['loginPassword'];
				$this->parameters['username'] = $row['loginUsername'];
			}
		}
	}
	
	public function searchForPurchasedItems() {
		if (empty($this->parameters['username']) || empty($this->parameters['password'])) {
			return array(
				'template' => $this->renderAuthorizationDialog(false)
			);
		}
		
		$request = new HTTPRequest('https://www.woltlab.com/api/1.0/customer/purchases/list.json', array(
			'method' => 'POST'
		), array(
			'username' => $this->parameters['username'],
			'password' => $this->parameters['password'],
			'wcfMajorReleases' => $this->parameters['wcfMajorReleases']
		));
		
		$request->execute();
		$reply = $request->getReply();
		$response = JSON::decode($reply['body']);
		
		$code = (isset($response['status'])) ? $response['status'] : 500;
		switch ($code) {
			case 200:
				if (empty($response['products'])) {
					return array(
						'noResults' => WCF::getLanguage()->get('wcf.acp.pluginstore.purchasedItems.noResults')
					);
				}
				else {
					WCF::getSession()->register('__pluginStoreProducts', $response['products']);
					
					return array(
						'redirectURL' => LinkHandler::getInstance()->getLink('PluginStorePurchasedItems')
					);
				}
			break;
			
			// authentication error
			case 401:
				return array(
					'template' => $this->renderAuthorizationDialog(true)
				);
			break;
			
			// any other kind of errors
			default:
				throw new SystemException(WCF::getLanguage()->getDynamicVariable('wcf.acp.pluginstore.api.error', array('status' => $code)));
			break;
		}
	}
	
	protected function renderAuthorizationDialog($rejected) {
		WCF::getTPL()->assign(array(
			'rejected' => $rejected
		));
		
		return WCF::getTPL()->fetch('pluginStoreAuthorization');
	}
}
