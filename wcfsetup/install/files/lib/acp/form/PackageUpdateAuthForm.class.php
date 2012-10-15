<?php
namespace wcf\acp\form;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\server\PackageUpdateServerEditor;
use wcf\system\exception\IllegalLinkException;
use wcf\system\package\PackageUpdateAuthorizationRequiredException;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Shows the package update authentification form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class PackageUpdateAuthForm extends ACPForm {
	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.package.canUpdatePackage', 'admin.system.package.canInstallPackage');
	
	/**
	 * @see	wcf\acp\form\ACPForm::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package';
	
	/**
	 * exception that has been thrown if an authorization for the package update
	 * server is needed
	 * @var	wcf\system\package\PackageUpdateAuthorizationRequiredException
	 */
	public $exception = null;
	
	/**
	 * login username for package update server
	 * @var	string
	 */
	public $loginUsername = '';
	
	/**
	 * login password for package update server
	 * @var	string
	 */
	public $loginPassword = '';
	
	/**
	 * indicates if login data is saved
	 * @var	integer
	 */
	public $saveAuthData = 0;
	
	/**
	 * id of the package update server
	 * @var	integer
	 */
	public $packageUpdateServerID = 0;
	
	/**
	 * url of the package update server
	 * @var	integer
	 */
	public $url = '';
	
	/**
	 * response header of the package update server
	 * @var	string
	 */
	public $header = '';
	
	/**
	 * realm part of the response header
	 * @var	string
	 */
	public $realm = '';
	
	/**
	 * response of the package update server
	 * @var	integer
	 */
	public $message = '';
	
	/**
	 * post parameters of the form
	 * @var	string
	 */
	public $postParameters = '';
	
	/**
	 * get parameters of the form
	 * @var	string
	 */
	public $getParameters = '';
	
	/**
	 * list of reserved parameters that may not be part of the post and get
	 * parameters
	 * @var	array<string>
	 */
	protected static $reservedParameters = array('s', 'packageID', 'page', 'form', 'action', 'packageUpdateServerID', 'loginUsername', 'loginPassword', 'saveAuthData', 'requestedPage', 'requestedForm', 'requestedAction');
	
	/**
	 * Creates a new PackageUpdateAuthForm object.
	 * 
	 * @param	wcf\system\package\PackageUpdateAuthorizationRequiredException		$exception
	 */
	public function __construct(PackageUpdateAuthorizationRequiredException $exception = null) {
		$this->exception = $exception;
		if ($this->exception !== null) {
			$this->packageUpdateServerID = $this->exception->getPackageUpdateServerID();
			$this->url = $this->exception->getURL();
			$this->header = $this->exception->getResponseHeader();
			
			// get message
			$this->message = $this->exception->getResponseContent();
			
			// find out response charset
			if (preg_match('/charset=([a-z0-9\-]+)/i', $this->header, $match)) {
				$charset = strtoupper($match[1]);
				if ($charset != 'UTF-8') $this->message = @StringUtil::convertEncoding($charset, 'UTF-8', $this->message);
			}
			
			// format message
			$this->message = nl2br(preg_replace("/\n{3,}/", "\n\n", StringUtil::unifyNewlines(StringUtil::trim(strip_tags($this->message)))));
		}
		
		parent::__construct();
	}
	
	/**
	 * @todo	This whole page is carzy, it's manipulating the requested page, but RequestHandler does not work this way
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['loginUsername'])) $this->loginUsername = $_REQUEST['loginUsername'];
		if (isset($_REQUEST['loginPassword'])) $this->loginPassword = $_REQUEST['loginPassword'];
		if (isset($_REQUEST['saveAuthData'])) $this->saveAuthData = intval($_REQUEST['saveAuthData']);
		if ($this->packageUpdateServerID == 0 && isset($_REQUEST['packageUpdateServerID'])) $this->packageUpdateServerID = intval($_REQUEST['packageUpdateServerID']);
		
		if (!empty($_REQUEST['requestedPage']) || !empty($_REQUEST['requestedForm']) || !empty($_REQUEST['requestedAction'])) {
			// get update server
			$updateServer = new PackageUpdateServer($this->packageUpdateServerID);
			if (!$updateServer->packageUpdateServerID) {
				throw new IllegalLinkException();
			}
			
			// update update server
			if ($this->saveAuthData) {
				$updateServerEditor = new PackageUpdateServerEditor($updateServer);
				$updateServerEditor->update(array(
					'loginUsername' => $this->loginUsername,
					'loginPassword' => $this->loginPassword
				));
			}
			
			// save auth data in session
			$authData = array(
				'authType' => 'Basic',
				'loginUsername' => $this->loginUsername,
				'loginPassword' => $this->loginPassword
			);
			
			// session data
			$packageUpdateAuthData = WCF::getSession()->getVar('packageUpdateAuthData');
			if ($packageUpdateAuthData === null) $packageUpdateAuthData = array();
			$packageUpdateAuthData[$this->packageUpdateServerID] = $authData;
			WCF::getSession()->register('packageUpdateAuthData', $packageUpdateAuthData);
			
			// remove form=PackageUpdateAuth
			unset($_REQUEST['form'], $_GET['form'], $_POST['form']);
			
			// set page/form/action
			if (!empty($_REQUEST['requestedPage'])) {
				$_POST['page'] = $_REQUEST['requestedPage'];
			}
			else if (!empty($_REQUEST['requestedForm'])) {
				$_POST['form'] = $_REQUEST['requestedForm'];
			}
			else {
				$_POST['action'] = $_REQUEST['requestedAction'];
			}
			
			// remove requestedPage...
			unset($_REQUEST['requestedPage'], $_REQUEST['requestedForm'], $_REQUEST['requestedAction']);
			
			// start request handler
			
			/**
			 * TODO: This is not working anymore, find a solution!
			 */
			
			global $packageDirs;
			RequestHandler::handle(ArrayUtil::appendSuffix(!empty($packageDirs) ? $packageDirs : array(WCF_DIR), 'lib/acp/'));
			exit;
		}
	}
	
	/**
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// extract realm
		if (preg_match('/realm="(.*?)"/i', $this->header, $match)) {
			$this->realm = $match[1];
		}
		
		// get existing auth data
		if ($this->packageUpdateServerID) {
			$updateServer = new PackageUpdateServer($this->packageUpdateServerID);
			$authData = $updateServer->getAuthData();
			if (isset($authData['loginUsername'])) $this->loginUsername = $authData['loginUsername'];
			if (isset($authData['loginPassword'])) $this->loginPassword = $authData['loginPassword'];
			
			if (isset($authData['loginUsername']) || isset($authData['loginPassword'])) {
				$this->errorField = 'loginPassword';
				$this->errorType = 'invalid';
			}
		}
		
		$this->buildPostParameters();
		$this->buildGetParameters();
		
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'loginUsername' => $this->loginUsername,
			'loginPassword' => $this->loginPassword,
			'saveAuthData' => $this->saveAuthData,
			'packageUpdateServerID' => $this->packageUpdateServerID,
			'url' => $this->url,
			'realm' => $this->realm,
			'message' => $this->message,
			'requestMethod' => WCF::getSession()->requestMethod,
			'postParameters' => $this->postParameters,
			'getParameters' => $this->getParameters
		));
	}
	
	/**
	 * Builds a list of POST parameters.
	 */
	protected function buildPostParameters() {
		$postParameters = array();
		
		if (isset($_POST['page'])) $postParameters['requestedPage'] = $_POST['page'];
		if (isset($_POST['form'])) $postParameters['requestedForm'] = $_POST['form'];
		if (isset($_POST['action'])) $postParameters['requestedAction'] = $_POST['action'];
		
		foreach ($_POST as $key => $value) {
			if (!in_array($key, self::$reservedParameters)) {
				$postParameters[$key] = $value;
			}
		}
		
		$this->buildPostParametersList($postParameters);
	}
	
	/**
	 * Builds a list of POST parameters.
	 * 
	 * @param	array		$parameters
	 * @param	string		$prefix
	 */
	protected function buildPostParametersList($parameters, $prefix = '') {
		foreach ($parameters as $key => $value) {
			$key = StringUtil::encodeHTML($key);
			if (is_array($value)) {
				$this->buildPostParametersList($value, (!empty($prefix) ? $prefix."[".$key."]" : $key));
			}
			else {
				$this->postParameters .= '<input type="hidden" name="'.(!empty($prefix) ? $prefix."[".$key."]" : $key).'" value="'.StringUtil::encodeHTML($value).'" />';
			}
		}
	}
	
	/**
	 * Builds a list of GET parameters.
	 */
	protected function buildGetParameters() {
		$getParameters = array();
		
		if (isset($_GET['page'])) $getParameters['requestedPage'] = $_GET['page'];
		if (isset($_GET['form'])) $getParameters['requestedForm'] = $_GET['form'];
		if (isset($_GET['action'])) $getParameters['requestedAction'] = $_GET['action'];
		
		foreach ($_GET as $key => $value) {
			if (!in_array($key, self::$reservedParameters)) {
				$getParameters[$key] = $value;
			}
		}
		
		$this->buildPostParametersList($getParameters);
	}
	
	/**
	 * Builds a list of GET parameters.
	 * 
	 * @param	array		$parameters
	 * @param	string		$prefix 
	 */
	protected function buildGetParametersList($parameters, $prefix = '') {
		foreach ($parameters as $key => $value) {
			$key = rawurlencode($key);
			if (is_array($value)) {
				$this->buildGetParametersList($value, (!empty($prefix) ? $prefix."[".$key."]" : $key));
			}
			else {
				if (!empty($this->getParameters)) $this->getParameters .= '&';
				$this->getParameters .= (!empty($prefix) ? $prefix."[".$key."]" : $key).'='.rawurlencode($value);
			}
		}
	}
}
