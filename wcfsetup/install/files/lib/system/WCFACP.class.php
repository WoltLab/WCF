<?php
namespace wcf\system;
use wcf\acp\form\MasterPasswordForm;
use wcf\acp\form\MasterPasswordInitForm;
use wcf\system\application\ApplicationHandler;
use wcf\system\event\EventHandler;
use wcf\system\exception\AJAXException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\request\RouteHandler;
use wcf\system\session\ACPSessionFactory;
use wcf\system\session\SessionHandler;
use wcf\system\template\ACPTemplateEngine;
use wcf\util\FileUtil;
use wcf\util\HeaderUtil;

/**
 * Extends WCF class with functions for the ACP.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system
 * @category	Community Framework
 */
class WCFACP extends WCF {
	/**
	 * Calls all init functions of the WCF and the WCFACP class. 
	 */
	public function __construct() {
		// add autoload directory
		self::$autoloadDirectories['wcf'] = WCF_DIR . 'lib/';
		
		// define tmp directory
		if (!defined('TMP_DIR')) define('TMP_DIR', FileUtil::getTempFolder());
		
		// start initialization
		$this->initMagicQuotes();
		$this->initDB();
		$this->loadOptions();
		$this->initPackage();
		$this->initSession();
		$this->initLanguage();
		$this->initTPL();
		$this->initCronjobs();
		$this->initCoreObjects();
		
		// prevent application loading during setup
		if (PACKAGE_ID) {
			$this->initApplications();
		}
		
		$this->initBlacklist();
		$this->initAuth();
		
		EventHandler::getInstance()->fireAction($this, 'initialized');
	}
	
	/**
	 * Does the user authentication.
	 */
	protected function initAuth() {
		// this is a work-around since neither RequestHandler
		// nor RouteHandler are populated right now
		$pathInfo = RouteHandler::getPathInfo();
		if (empty($pathInfo) || !preg_match('~^/(ACPCaptcha|Login|Logout)/~', $pathInfo)) {
			if (WCF::getUser()->userID == 0) {
				// work-around for AJAX-requests within ACP
				if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
					throw new AJAXException(WCF::getLanguage()->get('wcf.ajax.error.sessionExpired'), AJAXException::SESSION_EXPIRED, '');
				}
				
				// build redirect path
				$application = ApplicationHandler::getInstance()->getActiveApplication();
				if ($application === null) {
					throw new SystemException("You have aborted the installation, therefore this installation is unusable. You are required to reinstall the software.");
				}
				
				// fallback for unknown host (rescue mode)
				if ($application->domainName != $_SERVER['HTTP_HOST']) {
					$pageURL = RouteHandler::getProtocol() . $_SERVER['HTTP_HOST'] . RouteHandler::getPath(array('acp'));
				}
				else {
					$pageURL = $application->getPageURL();
				}
				
				$path = $pageURL . 'acp/index.php/Login/' . SID_ARG_1ST . '&url=' . rawurlencode(RouteHandler::getProtocol() . $_SERVER['HTTP_HOST'] . WCF::getSession()->requestURI);
				
				HeaderUtil::redirect($path);
				exit;
			}
			else {
				// work-around for AJAX-requests within ACP
				if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
					try {
						WCF::getSession()->checkPermissions(array('admin.general.canUseAcp'));
					}
					catch (PermissionDeniedException $e) {
						throw new AJAXException(self::getLanguage()->get('wcf.ajax.error.permissionDenied'), AJAXException::INSUFFICIENT_PERMISSIONS, $e->getTraceAsString());
					}
				}
				else {
					WCF::getSession()->checkPermissions(array('admin.general.canUseAcp'));
				}
				
				// force debug mode if in ACP and authenticated
				self::$overrideDebugMode = true;
			}
		}
	}
	
	/**
	 * @see	\wcf\system\WCF::initSession()
	 */
	protected function initSession() {
		$factory = new ACPSessionFactory();
		$factory->load();
		
		self::$sessionObj = SessionHandler::getInstance();
	}
	
	/**
	 * @see	\wcf\system\WCF::initTPL()
	 */
	protected function initTPL() {
		self::$tplObj = ACPTemplateEngine::getInstance();
		self::getTPL()->setLanguageID(self::getLanguage()->languageID);
		$this->assignDefaultTemplateVariables();
	}
	
	/**
	 * @see	\wcf\system\WCF::assignDefaultTemplateVariables()
	 */
	protected function assignDefaultTemplateVariables() {
		parent::assignDefaultTemplateVariables();
		
		// base tag is determined on runtime
		$host = RouteHandler::getHost();
		$path = RouteHandler::getPath();
		
		self::getTPL()->assign(array(
			'baseHref' => $host . $path
		));
	}
	
	/**
	 * Initializes the active package.
	 */
	protected function initPackage() {
		// define active package id
		if (!defined('PACKAGE_ID')) {
			define('PACKAGE_ID', 1);
		}
	}
	
	/**
	 * Checks whether the active user has entered the valid master password.
	 */
	public static function checkMasterPassword() {
		if (defined('MODULE_MASTER_PASSWORD') && MODULE_MASTER_PASSWORD == 1 && !WCF::getSession()->getVar('masterPassword')) {
			if (file_exists(WCF_DIR.'acp/masterPassword.inc.php')) {
				require_once(WCF_DIR.'acp/masterPassword.inc.php');
			}
			if (defined('MASTER_PASSWORD')) {
				$form = new MasterPasswordForm();
				$form->__run();
				exit;
			}
			else {
				$form = new MasterPasswordInitForm();
				$form->__run();
				exit;
			}
		}
	}
}
