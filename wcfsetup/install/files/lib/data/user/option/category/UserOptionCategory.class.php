<?php
namespace wcf\data\user\option\category;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a user option category.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Option\Category
 *
 * @property-read	integer		$categoryID
 * @property-read	integer		$packageID
 * @property-read	string		$categoryName
 * @property-read	string		$parentCategoryName
 * @property-read	integer		$showOrder
 * @property-read	string		$permissions
 * @property-read	string		$options
 */
class UserOptionCategory extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'user_option_category';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'categoryID';
	
	/**
	 * Returns the title of this category.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return $this->categoryName;
	}
	
	/**
	 * Returns an instance of UserOptionCategory by name.
	 * 
	 * @param	string		$categoryName
	 * @return	\wcf\data\user\option\category\UserOptionCategory
	 */
	public static function getCategoryByName($categoryName) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_user_option_category
			WHERE	categoryName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$categoryName]);
		$row = $statement->fetchArray();
		if ($row === false) return null;
		
		return new UserOptionCategory(null, $row);
	}
}
