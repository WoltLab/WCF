<?php
namespace wcf\system\devtools;
use wcf\system\SingletonFactory;
use wcf\util\FileUtil;
use wcf\util\JSON;

/**
 * Enables the rapid deployment of new installations using a central configuration file
 * in the document root. Requires the developer mode to work.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Devtools\Package
 * @since       3.1
 */
class DevtoolsSetup extends SingletonFactory {
	/**
	 * configuration file in the server's document root
	 * @var string
	 */
	const CONFIGURATION_FILE = 'wsc-dev-config-52.json';
	
	/**
	 * configuration data
	 * @var array
	 */
	protected $configuration = [];
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		if (empty($_SERVER['DOCUMENT_ROOT'])) return;
		
		$docRoot = FileUtil::addTrailingSlash(FileUtil::unifyDirSeparator($_SERVER['DOCUMENT_ROOT']));
		if (!file_exists($docRoot . self::CONFIGURATION_FILE)) return;
		
		$contents = file_get_contents($docRoot . self::CONFIGURATION_FILE);
		
		// allow the exception to go rampage
		$this->configuration = JSON::decode($contents);
	}
	
	/**
	 * Returns the database configuration.
	 * 
	 * @return array|null
	 */
	public function getDatabaseConfig() {
		if (!isset($this->configuration['setup']) || !isset($this->configuration['setup']['database'])) return null;
		
		// dirname return a single backslash on Windows if there are no parent directories 
		$dir = dirname($_SERVER['SCRIPT_NAME']);
		$dir = ($dir === '\\') ? '/' : FileUtil::addTrailingSlash($dir);
		if ($dir === '/') throw new \RuntimeException("Refusing to install in the document root.");
		
		$dir = FileUtil::removeLeadingSlash(FileUtil::removeTrailingSlash($dir));
		$dbName = implode('_', explode('/', $dir));
		
		$dbConfig = $this->configuration['setup']['database'];
		return [
			'auto' => $dbConfig['auto'],
			'host' => $dbConfig['host'],
			'password' => $dbConfig['password'],
			'username' => $dbConfig['username'],
			'dbName' => $dbName,
			'dbNumber' => $dbConfig['dbNumber']
		];
	}
	
	/**
	 * Returns true if the suggested default paths for the Core and, if exists,
	 * the bundled app should be used.
	 * 
	 * @return      boolean
	 */
	public function useDefaultInstallPath() {
		return (isset($this->configuration['setup']) && isset($this->configuration['setup']['useDefaultInstallPath']) && $this->configuration['setup']['useDefaultInstallPath'] === true);
	}
	
	/**
	 * Returns true if a static cookie prefix should be used, instead of the randomized
	 * value used for non-dev-mode installations.
	 * 
	 * @return      boolean
	 */
	public function forceStaticCookiePrefix() {
		return (isset($this->configuration['setup']) && isset($this->configuration['setup']['forceStaticCookiePrefix']) && $this->configuration['setup']['forceStaticCookiePrefix'] === true);
	}
	
	/**
	 * List of option values that will be set after the setup has completed.
	 * 
	 * @return      string[]
	 */
	public function getOptionOverrides() {
		if (!isset($this->configuration['configuration']) || empty($this->configuration['configuration']['option'])) return [];
		
		if (isset($this->configuration['configuration']['option']['cookie_prefix'])) {
			throw new \DomainException("The 'cookie_prefix' option cannot be set during the setup, consider using the 'forceStaticCookiePrefix' setting instead.");
		}
		
		return $this->configuration['configuration']['option'];
	}
	
	/**
	 * Returns a list of users that should be automatically created during setup.
	 * 
	 * @return      array|\Generator
	 */
	public function getUsers() {
		if (empty($this->configuration['user'])) return;
		
		foreach ($this->configuration['user'] as $user) {
			if ($user['username'] === 'root') throw new \LogicException("The 'root' user is automatically created.");
			
			yield [
				'username' => $user['username'],
				'password' => $user['password'],
				'email' => $user['email']
			];
		}
	}
	
	/**
	 * Returns the base path for projects that should be automatically imported.
	 * 
	 * @return      string
	 */
	public function getDevtoolsImportPath() {
		return (isset($this->configuration['configuration']['devtools']) && !empty($this->configuration['configuration']['devtools']['importFromPath'])) ? $this->configuration['configuration']['devtools']['importFromPath'] : '';
	}
	
	/**
	 * Returns the raw configuration data.
	 * 
	 * @return array
	 */
	public function getRawConfiguration() {
		return $this->configuration;
	}
}
