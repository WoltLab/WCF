<?php
namespace wcf\data\language\item;
use wcf\data\DatabaseObject;

/**
 * Represents a language item.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.language.item
 * @category	Community Framework
 *
 * @property-read	integer		$languageItemID
 * @property-read	integer		$languageID
 * @property-read	string		$languageItem
 * @property-read	string		$languageItemValue
 * @property-read	string		$languageCustomItemValue
 * @property-read	integer		$languageUseCustomValue
 * @property-read	integer		$languageItemOriginIsSystem
 * @property-read	integer		$languageCategoryID
 * @property-read	integer		$packageID
 */
class LanguageItem extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'language_item';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'languageItemID';
}
