<?php
namespace wcf\data\category;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\category\CategoryPermissionHandler;
use wcf\system\exception\PermissionDeniedException;

/**
 * Represents a viewable category.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.category
 * @category	Community Framework
 */
class ViewableCategory extends DatabaseObjectDecorator {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\category\Category';
	
	/**
	 * acl permissions for the active user of this category
	 * @var	array<boolean>
	 */
	protected $permissions = null;
	
	/**
	 * Checks if the active user has all given permissions and throws a 
	 * PermissionDeniedException if that isn't the case.
	 * 
	 * @param	array<string>		$permissions
	 */
	public function checkPermissions(array $permissions) {
		foreach ($permissions as $permission) {
			if (!$this->getPermission($permission)) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * Returns the acl permission value of the given permission for the active
	 * user and of this category.
	 * 
	 * @param	string		$permission
	 * @return	boolean
	 */
	public function getPermission($permission) {
		if ($this->permissions === null) {
			$this->permissions = CategoryPermissionHandler::getInstance()->getPermissions($this->getDecoratedObject());
		}
		
		if (isset($this->permissions[$permission])) {
			return $this->permissions[$permission];
		}
		
		return false;
	}
}
