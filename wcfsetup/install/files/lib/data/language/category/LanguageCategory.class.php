<?php
namespace wcf\data\language\category;
use wcf\data\DatabaseObject;

/**
 * Represents a language category.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Language\Category
 *
 * @property-read	integer		$languageCategoryID
 * @property-read	string		$languageCategory
 */
class LanguageCategory extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'language_category';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'languageCategoryID';
}
