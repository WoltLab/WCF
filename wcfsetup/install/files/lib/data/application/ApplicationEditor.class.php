<?php
namespace wcf\data\application;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\ApplicationCacheBuilder;
use wcf\system\WCF;

/**
 * Provides functions to edit applications.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.application
 * @category	Community Framework
 */
class ApplicationEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\application\Application';
	
	/**
	 * Sets current application as primary application.
	 */
	public function setAsPrimary() {
		$sql = "UPDATE	wcf".WCF_N."_application
			SET	isPrimary = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(0));
		
		$sql = "UPDATE	wcf".WCF_N."_application
			SET	isPrimary = ?
			WHERE	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			1,
			$this->packageID
		));
		
		self::resetCache();
	}
	
	/**
	 * Sets the first installed application as primary unless an other application already is primary.
	 */
	public static function setup() {
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_application
			WHERE	isPrimary = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(1));
		$row = $statement->fetchArray();
		
		if ($row['count']) {
			// there is already a primary application
			return;
		}
		else {
			// set first installed application as primary
			$sql = "SELECT		packageID
				FROM		wcf".WCF_N."_package
				WHERE		packageID <> ?
						AND isApplication = ?
				ORDER BY	installDate ASC";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				1,
				1
			));
			$row = $statement->fetchArray();
			
			if ($row !== false) {
				$sql = "UPDATE	wcf".WCF_N."_application
					SET	isPrimary = ?
					WHERE	packageID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute(array(
					1,
					$row['packageID']
				));
			}
		}
	}
	
	/**
	 * @see	\wcf\data\IEditableCachedObject::resetCache()
	 */
	public static function resetCache() {
		ApplicationCacheBuilder::getInstance()->reset();
	}
}
