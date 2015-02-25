<?php
namespace wcf\data\user\option\category;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a user option category.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.option.category
 * @category	Community Framework
 */
class UserOptionCategory extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'user_option_category';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'categoryID';
	
	/**
	 * @see	\wcf\data\DatabaseObject::__construct()
	 */
	public function __construct($categoryID, $row = null, UserOptionCategory $category = null) {
		if ($categoryID !== null) {
			$sql = "SELECT	option_category.*,
					(SELECT COUNT(DISTINCT optionName) FROM wcf".WCF_N."_user_option WHERE categoryName = option_category.categoryName) AS options
				FROM	wcf".WCF_N."_user_option_category option_category
				WHERE	option_category.categoryID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($categoryID));
			$row = $statement->fetchArray();
		}
		
		parent::__construct(null, $row, $category);
	}
	
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
		$statement->execute(array($categoryName));
		$row = $statement->fetchArray();
		if ($row === false) return null;
		
		return new UserOptionCategory(null, $row);
	}
}
