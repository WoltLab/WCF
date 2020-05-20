<?php
namespace wcf\acp\page;
use wcf\data\user\UserProfile;
use wcf\page\AbstractPage;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\builder\OptionCacheBuilder;
use wcf\system\io\RemoteFile;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the welcome page in admin control panel.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 */
class IndexPage extends AbstractPage {
	/**
	 * server information
	 * @var	string[]
	 */
	public $server = [];
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->server = [
			'os' => PHP_OS,
			'webserver' => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '',
			'mySQLVersion' => WCF::getDB()->getVersion(),
			'load' => '',
			'memoryLimit' => @ini_get('memory_limit'),
			'upload_max_filesize' => @ini_get('upload_max_filesize'),
			'postMaxSize' => @ini_get('post_max_size'),
			'sslSupport' => RemoteFile::supportsSSL()
		];
		
		// get load
		if (function_exists('sys_getloadavg')) {
			$load = sys_getloadavg();
			if (is_array($load) && count($load) == 3) {
				$this->server['load'] = implode(', ', $load);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$usersAwaitingApproval = 0;
		if (REGISTER_ACTIVATION_METHOD & UserProfile::REGISTER_ACTIVATION_ADMIN) {
			$sql = "SELECT	COUNT(*)
				FROM	wcf".WCF_N."_user
				WHERE	activationCode <> 0";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			$usersAwaitingApproval = $statement->fetchSingleColumn();
		}
		
		$recaptchaWithoutKey = false;
		$recaptchaKeyLink = '';
		if (CAPTCHA_TYPE == 'com.woltlab.wcf.recaptcha' && (!RECAPTCHA_PUBLICKEY || !RECAPTCHA_PRIVATEKEY)) {
			$recaptchaWithoutKey = true;
			
			$optionCategories = OptionCacheBuilder::getInstance()->getData([], 'categories');
			$categorySecurity = $optionCategories['security'];
			$recaptchaKeyLink = LinkHandler::getInstance()->getLink(
				'Option',
				[
					'id' => $categorySecurity->categoryID,
					'optionName' => 'recaptcha_publickey'
				], '#category_security.antispam'
			);
		}
		
		$evaluationExpired = $evaluationPending = [];
		foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
			if ($application->getPackage()->package === 'com.woltlab.wcf') {
				continue;
			}
			
			$app = WCF::getApplicationObject($application);
			$endDate = $app->getEvaluationEndDate();
			if ($endDate) {
				if ($endDate < TIME_NOW) {
					$pluginStoreFileID = $app->getEvaluationPluginStoreID();
					$isWoltLab = false;
					if ($pluginStoreFileID === 0 && strpos($application->getPackage()->package, 'com.woltlab.') === 0) {
						$isWoltLab = true;
					}
					
					$evaluationExpired[] = [
						'packageName' => $application->getPackage()->getName(),
						'isWoltLab' => $isWoltLab,
						'pluginStoreFileID' => $pluginStoreFileID
					];
				}
				else {
					if (!isset($evaluationPending[$endDate])) {
						$evaluationPending[$endDate] = [];
					}
					
					$evaluationPending[$endDate][] = $application->getPackage()->getName();
				}
			}
		}
		
		$missingLanguageItemsMTime = 0;
		if (
			ENABLE_DEBUG_MODE
			&& ENABLE_DEVELOPER_TOOLS
			&& file_exists(WCF_DIR . 'log/missingLanguageItems.txt')
			&& filesize(WCF_DIR . 'log/missingLanguageItems.txt') > 0
		) {
			$missingLanguageItemsMTime = filemtime(WCF_DIR . 'log/missingLanguageItems.txt');
		}
		
		WCF::getTPL()->assign([
			'recaptchaWithoutKey' => $recaptchaWithoutKey,
			'recaptchaKeyLink' => $recaptchaKeyLink,
			'server' => $this->server,
			'usersAwaitingApproval' => $usersAwaitingApproval,
			'evaluationExpired' => $evaluationExpired,
			'evaluationPending' => $evaluationPending,
			'missingLanguageItemsMTime' => $missingLanguageItemsMTime
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		// check package installation queue
		if ($this->action == 'WCFSetup') {
			$queueID = PackageInstallationDispatcher::checkPackageInstallationQueue();
			
			if ($queueID) {
				WCF::getTPL()->assign(['queueID' => $queueID]);
				WCF::getTPL()->display('packageInstallationSetup');
				exit;
			}
		}
		
		// show page
		parent::show();
	}
}
