<?php
namespace wcf\data\language\item;
use wcf\data\DatabaseObject;

/**
 * Represents a language item.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Language\Item
 *
 * @property-read	integer		$languageItemID			unique id of the language item
 * @property-read	integer		$languageID			id of the language the language item belongs to
 * @property-read	string		$languageItem			name and textual identifier of the language item
 * @property-read	string		$languageItemValue		default value of the language item 
 * @property-read	string		$languageCustomItemValue	custom value of the language item set by an admin
 * @property-read	integer		$languageUseCustomValue		is `1` if the custom value is used instead of the default value, otherwise `0`
 * @property-read	integer		$languageItemOriginIsSystem	is `1` if the language item has been delivered by a package, otherwise `0` (for example, if language item has been created for i18n content)
 * @property-read	integer		$languageCategoryID		id of the language category the language item belongs to
 * @property-read	integer|null	$packageID			id of the package the which delivers the language item or with which the language item is associated
 * @property-read       string          $languageItemOldValue           previous default value of the language item
 * @property-read       integer         $languageCustomItemDisableTime  the timestamp at which the custom version has been disabled due to a change to the original value
 * @property-read	integer		$isCustomLanguageItem		is `1` if the language item has been added manually via the ACP
 */
class LanguageItem extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'languageItemID';
}
