<?php
namespace wcf\data\option;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\OptionCacheBuilder;
use wcf\system\cache\CacheHandler;
use wcf\system\io\AtomicWriter;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Provides functions to edit options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Option
 * 
 * @method	Option		getDecoratedObject()
 * @mixin	Option
 */
class OptionEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * options cache file name
	 * @var	string
	 */
	const FILENAME = 'options.inc.php';
	
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Option::class;
	
	/**
	 * Imports the given options.
	 * 
	 * @param	array		$options	name to value
	 */
	public static function import(array $options) {
		// get option ids
		$sql = "SELECT		optionName, optionID
			FROM		wcf".WCF_N."_option";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$optionIDs = [];
		while ($row = $statement->fetchArray()) {
			$optionIDs[$row['optionName']] = $row['optionID'];
		}
		
		$newOptions = [];
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
		$sql = "SELECT	optionID, optionValue
			FROM	wcf".WCF_N."_option
			WHERE	optionName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(['cache_source_type']);
		$row = $statement->fetchArray();
		
		$sql = "UPDATE	wcf".WCF_N."_option
			SET	optionValue = ?
			WHERE	optionID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		$flushCache = false;
		WCF::getDB()->beginTransaction();
		foreach ($options as $id => $value) {
			if ($id == $row['optionID'] && ($value != $row['optionValue'] || $value != CACHE_SOURCE_TYPE)) {
				$flushCache = true;
			}
			
			$statement->execute([
				$value,
				$id
			]);
		}
		WCF::getDB()->commitTransaction();
		
		// force a cache reset if options were changed
		self::resetCache();
		
		// flush entire cache, as the CacheSource was changed
		if ($flushCache) {
			// flush caches (in case register_shutdown_function gets not properly called)
			CacheHandler::getInstance()->flushAll();
			
			// flush cache before finishing request to flush caches created after this was executed
			register_shutdown_function(function() {
				CacheHandler::getInstance()->flushAll();
			});
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		// reset cache
		OptionCacheBuilder::getInstance()->reset();
		
		// reset options.inc.php files
		self::rebuild();
	}
	
	/**
	 * Rebuilds the option file.
	 */
	public static function rebuild() {
		$writer = new AtomicWriter(WCF_DIR.'options.inc.php');
		
		// file header
		$writer->write("<?php\n/**\n* generated at ".gmdate('r')."\n*/\n");
		
		// get all options
		$options = Option::getOptions();
		foreach ($options as $optionName => $option) {
			$writer->write("if (!defined('".$optionName."')) define('".$optionName."', ".(($option->optionType == 'boolean' || $option->optionType == 'integer') ? intval($option->optionValue) : "'".addcslashes($option->optionValue, "'\\")."'").");\n");
		}
		unset($options);
		
		// add a pseudo option that indicates that option file has been written properly
		$writer->write("if (!defined('WCF_OPTION_INC_PHP_SUCCESS')) define('WCF_OPTION_INC_PHP_SUCCESS', true);");
		
		// file footer
		$writer->write("\n");
		$writer->flush();
		$writer->close();
		
		FileUtil::makeWritable(WCF_DIR.'options.inc.php');
		WCF::resetZendOpcache(WCF_DIR.'options.inc.php');
	}
}
