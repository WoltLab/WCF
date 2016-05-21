<?php
namespace wcf\data\user\profile\menu\item;
use wcf\system\menu\user\profile\content\IUserProfileMenuContent;
use wcf\data\DatabaseObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\data\TDatabaseObjectPermissions;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;

/**
 * Represents an user profile menu item.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.profile.menu.item
 * @category	Community Framework
 *
 * @property-read	integer		$menuItemID
 * @property-read	integer		$packageID
 * @property-read	string		$menuItem
 * @property-read	integer		$showOrder
 * @property-read	string		$permissions
 * @property-read	string		$options
 * @property-read	string		$className
 */
class UserProfileMenuItem extends DatabaseObject {
	use TDatabaseObjectOptions;
	use TDatabaseObjectPermissions;
	
	/**
	 * content manager
	 * @var	IUserProfileMenuContent
	 */
	protected $contentManager = null;
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'user_profile_menu_item';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'menuItemID';
	
	/**
	 * Returns the item identifier, dots are replaced by underscores.
	 * 
	 * @return	string
	 */
	public function getIdentifier() {
		return str_replace('.', '_', $this->menuItem);
	}
	
	/**
	 * Returns the content manager for this menu item.
	 * 
	 * @return	IUserProfileMenuContent
	 * @throws	SystemException
	 */
	public function getContentManager() {
		if ($this->contentManager === null) {
			if (!class_exists($this->className)) {
				throw new SystemException("Unable to find class '".$this->className."'");
			}
			
			if (!is_subclass_of($this->className, SingletonFactory::class)) {
				throw new SystemException("'".$this->className."' does not extend '".SingletonFactory::class."'");
			}
			
			if (!is_subclass_of($this->className, IUserProfileMenuContent::class)) {
				throw new SystemException("'".$this->className."' does not implement '".IUserProfileMenuContent::class."'");
			}
			
			$this->contentManager = call_user_func([$this->className, 'getInstance']);
		}
		
		return $this->contentManager;
	}
}
