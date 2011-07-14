<?php
namespace wcf\data\user\option\category;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a user option category.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2010 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.option.category
 * @category 	Community Framework
 */
class UserOptionCategory extends DatabaseObject {
	/**
	 * @see	DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'user_option_category';
	
	/**
	 * @see	DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'categoryID';
	
	/**
	 * @todo	Methode so beibehalten und effektiv DatabaseObject::__construct() klonen
	 * 		oder generell so etwas mit einem zusätzlichen Query abfragen?
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
	 * Returns an instance of UserOptionCategory by name and package id.
	 * 
	 * @param	string		$categoryName
	 * @param	integer		$packageID
	 * @return	UserOptionCategory
	 */
	public static function getCategoryByName($categoryName, $packageID) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_user_option_category
			WHERE	categoryName = ?
				AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($categoryName, $packageID));
		$row = $statement->fetchArray();
		if (!$row) $row = array();
		
		return new UserOptionCategory(null, $row);
	}
}
