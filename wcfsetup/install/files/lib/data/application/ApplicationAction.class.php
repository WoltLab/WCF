<?php
namespace wcf\data\application;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\cache\builder\ApplicationCacheBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Executes application-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Application
 * 
 * @method	Application		create()
 * @method	ApplicationEditor[]	getObjects()
 * @method	ApplicationEditor	getSingleObject()
 */
class ApplicationAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = ApplicationEditor::class;
	
	/**
	 * application editor object
	 * @var	ApplicationEditor
	 */
	public $applicationEditor;
	
	/**
	 * Assigns a list of applications to a group and computes cookie domain and path.
	 */
	public function rebuild() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		$sql = "UPDATE	wcf".WCF_N."_application
			SET	cookieDomain = ?,
				cookiePath = ?
			WHERE	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		// calculate cookie path
		$domains = [];
		$regex = new Regex(':[0-9]+');
		foreach ($this->getObjects() as $application) {
			$domainName = $application->domainName;
			if (StringUtil::endsWith($regex->replace($domainName, ''), $application->cookieDomain)) {
				$domainName = $application->cookieDomain;
			}
			
			if (!isset($domains[$domainName])) {
				$domains[$domainName] = [];
			}
			
			$domains[$domainName][$application->packageID] = explode('/', FileUtil::removeLeadingSlash(FileUtil::removeTrailingSlash($application->domainPath)));
		}
		
		WCF::getDB()->beginTransaction();
		foreach ($domains as $domainName => $data) {
			$path = null;
			foreach ($data as $domainPath) {
				if ($path === null) {
					$path = $domainPath;
				}
				else {
					foreach ($path as $i => $part) {
						if (!isset($domainPath[$i]) || $domainPath[$i] != $part) {
							// remove all following elements including current one
							foreach ($path as $j => $innerPart) {
								if ($j >= $i) {
									unset($path[$j]);
								}
							}
							
							// skip to next domain
							continue 2;
						}
					}
				}
			}
			
			$path = FileUtil::addLeadingSlash(FileUtil::addTrailingSlash(implode('/', $path)));
			
			foreach (array_keys($data) as $packageID) {
				$statement->execute([
					$domainName,
					$path,
					$packageID
				]);
			}
		}
		WCF::getDB()->commitTransaction();
		
		// rebuild templates
		LanguageFactory::getInstance()->deleteLanguageCache();
		
		// reset application cache
		ApplicationCacheBuilder::getInstance()->reset();
	}
}
