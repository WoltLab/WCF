<?php
namespace wcf\data\language\category;
use wcf\data\DatabaseObject;
use wcf\data\ITitledObject;

/**
 * Represents a language category.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Language\Category
 *
 * @property-read	integer		$languageCategoryID	unique id of the language category
 * @property-read	string		$languageCategory	name and textual identifier of the language category
 */
class LanguageCategory extends DatabaseObject implements ITitledObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'languageCategoryID';
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->languageCategory;
	}
}
