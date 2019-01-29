<?php
namespace wcf\acp\page;
use wcf\data\application\Application;
use wcf\page\AbstractPage;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Shows the style list page.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Acp\Page
 * @since       5.2
 */
class SystemCheckPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.systemCheck';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.package.canInstallPackage'];
	
	/**
	 * A list of directories that need to be writable at all times, grouped by their application
	 * identifier. Only the directory itself is checked, unless the path ends with `/*`.
	 * @var string[][]
	 */
	public $directories = [
		'wcf' => [
			'/',
			'/acp/style',
			'/acp/templates/compiled',
			'/attachments',
			'/cache',
			'/images/*',
			'/language',
			'/log',
			'/media_files/*',
			'/sitemaps',
			'/style',
			'/templates/compiled',
			'/tmp',
		],
	];
	
	public $mysqlVersions = [
		'mysql' => '5.5.35',
		'mariadb' => [
			// MariaDB 5.5.47+ or 10.0.22+ are required
			// https://jira.mariadb.org/browse/MDEV-8756
			'5' => '5.5.47',
			'10' => '10.0.22',
		],
	];
	
	public $phpExtensions = [
		'mbstring',
		'libxml',
		'dom',
		'zlib',
		'pdo',
		'pdo_mysql',
		'json',
		'pcre',
		'gd',
		'hash',
	];
	
	public $phpMemoryLimit = 128;
	
	public $phpVersions = [
		'minimum' => '7.0.22',
		'sufficient' => ['7.0'],
		'recommended' => ['7.1', '7.2', '7.3'],
	];
	
	public $results = [
		'directories' => [],
		'mysql' => [
			'innodb' => false,
			'mariadb' => false,
			'result' => false,
			'version' => '0.0.0',
		],
		'php' => [
			'extension' => [],
			'memoryLimit' => [
				'required' => '0',
				'result' => false,
				'value' => '0',
			],
			'sha256' => false,
			'version' => [
				'result' => 'unsupported',
				'value' => '0.0.0',
			],
		],
		'status' => [
			'directories' => false,
			'mysql' => false,
			'php' => false,
		],
	];
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (IMAGE_ADAPTER_TYPE === 'imagick' && !in_array('imagick', $this->phpExtensions)) {
			$this->phpExtensions[] = 'imagick';
		}
		
		$this->validateMysql();
		$this->validatePhpExtensions();
		$this->validatePhpMemoryLimit();
		$this->validatePhpVersion();
		$this->validateWritableDirectories();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'mysqlVersions' => $this->mysqlVersions,
			'phpExtensions' => $this->phpExtensions,
			'phpMemoryLimit' => $this->phpMemoryLimit,
			'phpVersions' => $this->phpVersions,
			'results' => $this->results,
		]);
	}
	
	protected function validateMysql() {
		// check sql version
		$sqlVersion = WCF::getDB()->getVersion();
		$compareSQLVersion = preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', $sqlVersion);
		// Do not use the "raw" version, it usually contains a lot of noise.
		$this->results['mysql']['version'] = $compareSQLVersion;
		if (stripos($sqlVersion, 'MariaDB') !== false) {
			$this->results['mysql']['mariadb'] = true;
			
			// MariaDB has some legacy version that use the major version '5'.
			if ($compareSQLVersion[0] === '5') {
				$this->results['mysql']['result'] = (version_compare($compareSQLVersion, $this->mysqlVersions['mariadb']['5']) >= 0);
			}
			else {
				$this->results['mysql']['result'] = (version_compare($compareSQLVersion, $this->mysqlVersions['mariadb']['10']) >= 0);
			}
		}
		else if (version_compare($compareSQLVersion, $this->mysqlVersions['mysql']) >= 0) {
			$this->results['mysql']['result'] = true;
		}
		
		// check innodb support
		$sql = "SHOW ENGINES";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			if ($row['Engine'] == 'InnoDB' && in_array($row['Support'], ['DEFAULT', 'YES'])) {
				$this->results['mysql']['innodb'] = true;
				break;
			}
		}
		
		if ($this->results['mysql']['result'] && $this->results['mysql']['innodb']) {
			$this->results['status']['mysql'] = true;
		}
	}
	
	protected function validatePhpExtensions() {
		foreach ($this->phpExtensions as $phpExtension) {
			$result = extension_loaded($phpExtension);
			if (!$result) {
				$this->results['php']['extension'][] = $phpExtension;
			}
		}
		
		if (extension_loaded('hash')) {
			$this->results['php']['sha256'] = in_array('sha256', hash_algos());
		}
		
		$this->results['status']['php'] = empty($this->results['php']['extension']) && $this->results['php']['sha256'];
	}
	
	protected function validatePhpMemoryLimit() {
		$this->results['php']['memoryLimit']['required'] = $this->phpMemoryLimit . 'M';
		
		$memoryLimit = ini_get('memory_limit');
		
		// Memory is not limited through PHP.
		if ($memoryLimit == -1) {
			$this->results['php']['memoryLimit']['value'] = '∞';
			$this->results['php']['memoryLimit']['result'] = true;
		}
		else {
			// Completely numeric, PHP assumes this to be a value in bytes.
			if (is_numeric($memoryLimit)) {
				$memoryLimit = $memoryLimit / 1024 / 1024;
				
				$this->results['php']['memoryLimit']['value'] = $memoryLimit . 'M';
				$this->results['php']['memoryLimit']['result'] = ($memoryLimit >= $this->phpMemoryLimit);
			}
			else {
				// PHP supports the 'K', 'M' and 'G' shorthand notations.
				if (preg_match('~^(\d+)([KMG])$~', $memoryLimit, $matches)) {
					switch ($matches[2]) {
						case 'K':
							$memoryLimit = $matches[1] * 1024;
							
							$this->results['php']['memoryLimit']['value'] = $memoryLimit . 'M';
							$this->results['php']['memoryLimit']['result'] = ($memoryLimit >= $this->phpMemoryLimit);
							break;
						
						case 'M':
							$this->results['php']['memoryLimit']['value'] = $memoryLimit;
							$this->results['php']['memoryLimit']['result'] = ($matches[1] >= $this->phpMemoryLimit);
							break;
						
						case 'G':
							$this->results['php']['memoryLimit']['value'] = $memoryLimit;
							$this->results['php']['memoryLimit']['result'] = ($matches[1] * 1024 >= $this->phpMemoryLimit);
							break;
							
						default:
							$this->results['php']['memoryLimit']['value'] = $memoryLimit;
							$this->results['php']['memoryLimit']['result'] = false;
							return;
					}
				}
			}
		}
		
		$this->results['status']['php'] = $this->results['status']['php'] && $this->results['php']['memoryLimit']['result'];
	}
	
	protected function validatePhpVersion() {
		$phpVersion = phpversion();
		$comparePhpVersion = preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', $phpVersion);
		// Do not use the "raw" version, it usually contains a lot of noise.
		$this->results['php']['version']['value'] = $comparePhpVersion;
		if (version_compare($comparePhpVersion, $this->phpVersions['minimum']) >= 0) {
			$majorMinor = preg_replace('~^(\d+\.\d+).*$~', '\\1', $phpVersion);
			foreach (['recommended', 'sufficient'] as $type) {
				foreach ($this->phpVersions[$type] as $version) {
					if ($majorMinor === $version) {
						$this->results['php']['version']['result'] = $type;
						break 2;
					}
				}
			}
		}
		else {
			$this->results['php']['version']['result'] = 'unsupported';
		}
		
		$this->results['status']['php'] = $this->results['status']['php'] && ($this->results['php']['version']['result'] !== 'unsupported');
	}
	
	protected function validateWritableDirectories() {
		foreach ($this->directories as $abbreviation => $directories) {
			$basePath = Application::getDirectory($abbreviation);
			foreach ($directories as $directory) {
				$recursive = false;
				if (preg_match('~(.*)/\*$~', $directory, $matches)) {
					$recursive = true;
					$directory = $matches[1];
				}
				
				$path = $basePath . FileUtil::removeLeadingSlash(FileUtil::addTrailingSlash($directory));
				if ($this->checkDirectory($path) && $recursive) {
					$rdi = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
					$it = new \RecursiveIteratorIterator($rdi, \RecursiveIteratorIterator::SELF_FIRST);
					/** @var \SplFileInfo $item */
					foreach ($it as $item) {
						if ($item->isDir()) {
							$this->makeDirectoryWritable($item->getPathname());
						}
					}
				}
			}
		}
		
		$this->results['status']['directories'] = empty($this->results['directories']);
	}
	
	protected function checkDirectory($path) {
		if (!$this->createDirectoryIfNotExists($path)) {
			$this->results['directories'][] = FileUtil::unifyDirSeparator($path);
			return false;
		}
		
		return $this->makeDirectoryWritable($path);
	}
	
	protected function createDirectoryIfNotExists($path) {
		if (!file_exists($path) && !FileUtil::makePath($path)) {
			// FileUtil::makePath() returns false if either the directory cannot be created
			// or if it cannot be made writable.
			if (!file_exists($path)) {
				return false;
			}
		}
		
		return true;
	}
	
	protected function makeDirectoryWritable($path) {
		try {
			FileUtil::makeWritable($path);
		}
		catch (SystemException $e) {
			$this->results['directories'][] = FileUtil::unifyDirSeparator($path);
			return false;
		}
		
		return true;
	}
}
