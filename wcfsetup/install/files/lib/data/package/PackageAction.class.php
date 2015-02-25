<?php
namespace wcf\data\package;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\io\RemoteFile;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HTTPRequest;
use wcf\util\JSON;

/**
 * Executes package-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
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
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = array('searchForPurchasedItems');
	
	/**
	 * Validates parameters to search for purchased items in the WoltLab Plugin-Store.
	 */
	public function validateSearchForPurchasedItems() {
		WCF::getSession()->checkPermissions(array('admin.system.package.canInstallPackage', 'admin.system.package.canUpdatePackage'));
		
		$this->readString('password', true);
		$this->readString('username', true);
		
		if (empty($this->parameters['username'])) {
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("serverURL IN (?)", array(array('http://store.woltlab.com/maelstrom/', 'http://store.woltlab.com/typhoon/')));
			$conditions->add("loginUsername <> ''");
			$conditions->add("loginPassword <> ''");
			
			// check if user has already provided credentials
			$sql = "SELECT	loginUsername, loginPassword
				FROM	wcf".WCF_N."_package_update_server
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql, 1);
			$statement->execute($conditions->getParameters());
			$row = $statement->fetchArray();
			if (!empty($row['loginUsername']) && !empty($row['loginPassword'])) {
				$this->parameters['password'] = $row['loginPassword'];
				$this->parameters['username'] = $row['loginUsername'];
			}
		}
	}
	
	/**
	 * Searches for purchased items in the WoltLab Plugin-Store.
	 * 
	 * @return	array<string>
	 */
	public function searchForPurchasedItems() {
		if (!RemoteFile::supportsSSL()) {
			return array(
				'noSSL' => WCF::getLanguage()->get('wcf.acp.pluginStore.api.noSSL')
			);
		}
		
		if (empty($this->parameters['username']) || empty($this->parameters['password'])) {
			return array(
				'template' => $this->renderAuthorizationDialog(false)
			);
		}
		
		$request = new HTTPRequest('https://api.woltlab.com/1.0/customer/purchases/list.json', array(
			'method' => 'POST'
		), array(
			'username' => $this->parameters['username'],
			'password' => $this->parameters['password'],
			'wcfVersion' => WCF_VERSION
		));
		
		$request->execute();
		$reply = $request->getReply();
		$response = JSON::decode($reply['body']);
		
		$code = (isset($response['status'])) ? $response['status'] : 500;
		switch ($code) {
			case 200:
				if (empty($response['products'])) {
					return array(
						'noResults' => WCF::getLanguage()->get('wcf.acp.pluginStore.purchasedItems.noResults')
					);
				}
				else {
					WCF::getSession()->register('__pluginStoreProducts', $response['products']);
					WCF::getSession()->register('__pluginStoreWcfMajorReleases', $response['wcfMajorReleases']);
					
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
				throw new SystemException(WCF::getLanguage()->getDynamicVariable('wcf.acp.pluginStore.api.error', array('status' => $code)));
			break;
		}
	}
	
	/**
	 * Renders the authentication dialog.
	 * 
	 * @param	boolean		$rejected
	 * @return	string
	 */
	protected function renderAuthorizationDialog($rejected) {
		WCF::getTPL()->assign(array(
			'rejected' => $rejected
		));
		
		return WCF::getTPL()->fetch('pluginStoreAuthorization');
	}
}
