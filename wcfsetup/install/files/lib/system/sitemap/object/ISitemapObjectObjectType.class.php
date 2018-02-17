<?php
namespace wcf\system\sitemap\object;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;

/**
 * Interface for sitemap objects.
 * 
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Sitemap\Object
 * @since	3.1
 */
interface ISitemapObjectObjectType {
	/**
	 * Returns the DatabaseObject class name for the sitemap object.
	 *
	 * @return 	string
	 */
	public function getObjectClass();

	/**
	 * Returns the DatabaseObjectList class name for the sitemap object.
	 *
	 * @return 	string
	 */
	public function getObjectListClass();

	/**
	 * Returns the DatabaseObjectList for the sitemap object.
	 *
	 * @return 	DatabaseObjectList
	 */
	public function getObjectList();

	/**
	 * Returns the database column, which represents the last modified date.
	 * If there isn't any column, this method should return `null`.
	 *
	 * @return 	string|null
	 */
	public function getLastModifiedColumn();

	/**
	 * Returns the permission for a guest to view a certain object for this object type. 
	 *
	 * @param 	DatabaseObject 	$object
	 * @return 	boolean
	 */
	public function canView(DatabaseObject $object);
	
	/**
	 * Checks the requirements (e.g. module options) for this object type.
	 *
	 * @return 	boolean
	 */
	public function isAvailableType(); 
}
