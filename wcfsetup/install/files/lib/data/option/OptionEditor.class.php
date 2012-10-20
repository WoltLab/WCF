<?php
namespace wcf\data\option;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\CacheHandler;
use wcf\system\io\File;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Provides functions to edit options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.option
 * @category	Community Framework
 */
class OptionEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * options cache file name
	 * @var	string
	 */
	const FILENAME = 'options.inc.php';
	
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\option\Option';
	
	/**
	 * Imports the given options.
	 * 
	 * @param	array		$options	name to value
	 */
	public static function import(array $options) {
		// get option ids
		$sql = "SELECT		optionName, optionID
			FROM		wcf".WCF_N."_option option_table
			LEFT JOIN	wcf".WCF_N."_package_dependency package_dependency
			ON		(package_dependency.dependency = option_table.packageID)
			WHERE		package_dependency.packageID = ?
			ORDER BY	package_dependency.priority ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(PACKAGE_ID));
		$optionIDs = array();
		while ($row = $statement->fetchArray()) {
			$optionIDs[$row['optionName']] = $row['optionID'];
		}
		
		$newOptions = array();
		foreach ($options as $name => $value) {
			if (isset($optionIDs[$name])) {
				$newOptions[$optionIDs[$name]] = $value;
			}
		}
		
		self::updateAll($newOptions);
	}
	
	/**
	 * Updates the values of the given options.
	 * 
	 * @param	array		$options	id to value
	 */
	public static function updateAll(array $options) {
		$sql = "UPDATE	wcf".WCF_N."_option
			SET	optionValue = ?
			WHERE	optionID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		foreach ($options as $id => $value) {
			$statement->execute(array(
				$value,
				$id
			));
		}
		
		// force a cache reset if options were changed
		self::resetCache();
	}
	
	/**
	 * @see	wcf\data\IEditableCachedObject::resetCache()
	 */
	public static function resetCache() {
		// reset cache
		CacheHandler::getInstance()->clear(WCF_DIR.'cache', 'cache.option-*.php');
		
		// reset options.inc.php files
		$sql = "SELECT	package, packageID, packageDir
			FROM	wcf".WCF_N."_package
			WHERE	isApplication = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(1));
		while ($row = $statement->fetchArray()) {
			if ($row['package'] == 'com.woltlab.wcf') $packageDir = WCF_DIR;
			else $packageDir = FileUtil::getRealPath(WCF_DIR.$row['packageDir']);
			$filename = FileUtil::addTrailingSlash($packageDir).self::FILENAME;
			if (file_exists($filename)) {
				if (!@touch($filename, 1)) {
					if (!@unlink($filename)) {
						self::rebuildFile($filename, $row['packageID']);
					}
				}
			}
		}
	}
	
	/**
	 * Rebuilds cached options
	 *
	 * @param	string		$filename
	 * @param	integer		$packageID
	 */
	public static function rebuildFile($filename, $packageID = PACKAGE_ID) {
		$buffer = '';
		
		// file header
		$buffer .= "<?php\n/**\n* generated at ".gmdate('r')."\n*/\n";
		
		// get all options
		$options = Option::getOptions($packageID);
		foreach ($options as $optionName => $option) {
			$buffer .= "if (!defined('".$optionName."')) define('".$optionName."', ".(($option->optionType == 'boolean' || $option->optionType == 'integer') ? intval($option->optionValue) : "'".addcslashes($option->optionValue, "'\\")."'").");\n";
		}
		unset($options);
		
		// file footer
		$buffer .= "?>";
		
		// open file
		$file = new File($filename);
		
		// write buffer
		$file->write($buffer);
		unset($buffer);
		
		// close file
		$file->close();
		@$file->chmod(0777);
	}
}
