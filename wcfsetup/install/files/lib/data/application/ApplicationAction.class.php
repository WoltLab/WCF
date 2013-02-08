<?php
namespace wcf\data\application;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Executes application-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.application
 * @category	Community Framework
 */
class ApplicationAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\application\ApplicationEditor';
	
	/**
	 * application editor object
	 * @var	wcf\data\application\ApplicationEditor
	 */
	public $applicationEditor = null;
	
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
		$domains = array();
		foreach ($this->objects as $application) {
			if (!isset($domains[$application->domainName])) {
				$domains[$application->domainName] = array();
			}
			
			$domains[$application->domainName][$application->packageID] = explode('/', FileUtil::removeLeadingSlash(FileUtil::removeTrailingSlash($application->domainPath)));
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
				$statement->execute(array(
					$domainName,
					$path,
					$packageID
				));
			}
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * Validates parameters to set an application as primary.
	 */
	public function validateSetAsPrimary() {
		WCF::getSession()->checkPermissions(array('admin.system.canManageApplication'));
		
		$this->applicationEditor = $this->getSingleObject();
		if (!$this->applicationEditor->packageID || $this->applicationEditor->packageID == 1) {
			throw new UserInputException('objectIDs');
		}
		else if ($this->applicationEditor->isPrimary) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Sets an application as primary.
	 */
	public function setAsPrimary() {
		$this->applicationEditor->setAsPrimary();
	}
}
