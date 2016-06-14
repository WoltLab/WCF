<?php
namespace wcf\data\language\item;
use wcf\data\DatabaseObject;

/**
 * Represents a language item.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Language\Item
 *
 * @property-read	integer		$languageItemID
 * @property-read	integer		$languageID
 * @property-read	string		$languageItem
 * @property-read	string		$languageItemValue
 * @property-read	string		$languageCustomItemValue
 * @property-read	integer		$languageUseCustomValue
 * @property-read	integer		$languageItemOriginIsSystem
 * @property-read	integer		$languageCategoryID
 * @property-read	integer|null	$packageID
 */
class LanguageItem extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'language_item';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'languageItemID';
}
