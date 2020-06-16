<?php
namespace wcf\data\option;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\application\ApplicationHandler;
use wcf\system\email\transport\SmtpEmailTransport;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Executes option-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Option
 *  
 * @method	Option		create()
 * @method	OptionEditor[]	getObjects()
 * @method	OptionEditor	getSingleObject()
 */
class OptionAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = OptionEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.configuration.canEditOption'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.configuration.canEditOption'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.configuration.canEditOption'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'emailSmtpTest', 'import', 'update', 'updateAll', 'generateRewriteRules'];
	
	/**
	 * Validates permissions and parameters.
	 */
	public function validateImport() {
		parent::validateCreate();
	}
	
	/**
	 * Validates permissions and parameters.
	 */
	public function validateUpdateAll() {
		parent::validateCreate();
	}
	
	/**
	 * Imports options.
	 */
	public function import() {
		// create data
		call_user_func([$this->className, 'import'], $this->parameters['data']);
	}
	
	/**
	 * Updates the value of all given options.
	 */
	public function updateAll() {
		// create data
		call_user_func([$this->className, 'updateAll'], $this->parameters['data']);
	}
	
	/**
	 * Validates the basic SMTP connection parameters.
	 * 
	 * @throws      UserInputException
	 */
	public function validateEmailSmtpTest() {
		WCF::getSession()->checkPermissions($this->permissionsUpdate);
		
		$this->readString('host');
		$this->readInteger('port');
		$this->readString('startTls');
		
		$this->readString('user', true);
		$this->readString('password', true);
		if (!empty($this->parameters['user']) && empty($this->parameters['password'])) {
			throw new UserInputException('password');
		}
		else if (empty($this->parameters['user']) && !empty($this->parameters['password'])) {
			throw new UserInputException('user');
		}
	}
	
	/**
	 * Runs a simple test of the SMTP connection.
	 * 
	 * @return      string[]
	 */
	public function emailSmtpTest() {
		$smtp = new SmtpEmailTransport(
			$this->parameters['host'],
			$this->parameters['port'],
			$this->parameters['user'],
			$this->parameters['password'],
			$this->parameters['startTls']
		);
		
		return ['validationResult' => $smtp->testConnection()];
	}
	
	public function validateGenerateRewriteRules() {
		WCF::getSession()->checkPermissions(['admin.configuration.canEditOption']);
	}
	
	/**
	 * Returns a list of code-bbcode-containers containing the necessary
	 * rewrite rules
	 *
	 * @return string
	 */
	public function generateRewriteRules() {
		return WCF::getTPL()->fetch('__optionRewriteRulesOutput', 'wcf', [
			'rewriteRules' => $this->fetchRewriteRules(),
		]);
	}
	
	/**
	 * Returns an array with rewrite rules per necessary directory/file
	 * Applications in sub-directories of another application will be mapped to the top one
	 *
	 * @return string[][]
	 */
	protected function fetchRewriteRules() {
		$dirs = [];
		$rules = [
			'apache' => [],
		];
		foreach (ApplicationHandler::getInstance()->getApplications() as $app) {
			$test = $app->getPackage()->getAbsolutePackageDir();
			$insert = true;
			
			foreach ($dirs as $dir => $apps) {
				if (strpos($dir, $test) !== false) {
					unset($dirs[$dir]);
				}
				else if (strpos($test, $dir) !== false) {
					$insert = false;
					break;
				}
			}
			
			if ($insert) $dirs[$test] = [];
		}
		
		foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
			foreach ($dirs as $dir => $value) {
				if (strpos($application->getPackage()->getAbsolutePackageDir(), $dir) !== false) {
					$dirs[$dir][$application->domainPath] = $application->getPackage()->getAbsolutePackageDir();
				}
			}
		}
		
		foreach ($dirs as $dir => $domainPaths) {
			krsort($domainPaths);
			
			foreach ($domainPaths as $domainPath => $value) {
				$htaccess = "{$dir}.htaccess";
				$path = FileUtil::addTrailingSlash(substr($value, strlen($dir)));
				if ($path == '/') $path = '';
				$content = <<<SNIPPET
RewriteCond %{SCRIPT_FILENAME} !-d
RewriteCond %{SCRIPT_FILENAME} !-f
RewriteRule ^{$path}(.*)$ {$path}index.php?$1 [L,QSA]


SNIPPET;
				if (empty($rules['apache'][$htaccess])) $rules['apache'][$htaccess] = $content;
				else $rules['apache'][$htaccess] .= $content;
			}
		}
		
		return $rules;
	}
}
