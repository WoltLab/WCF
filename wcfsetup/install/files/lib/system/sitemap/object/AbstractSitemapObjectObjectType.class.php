<?php
namespace wcf\system\sitemap\object;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObject;

/**
 * Abstract implementation of a sitemap object.
 * 
 * @author	Joshua Ruesweg
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Sitemap\Object
 * @since	3.1
 */
abstract class AbstractSitemapObjectObjectType implements ISitemapObjectObjectType {
	/**
	 * A guest user profile.
	 * @var UserProfile
	 */
	protected static $userProfile = null;
	
	/**
	 * @inheritDoc
	 */
	public function getObjectListClass() {
		return $this->getObjectClass() . 'List';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectList() {
		$className = $this->getObjectListClass();
		return new $className;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLastModifiedColumn() {
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function canView(DatabaseObject $object) {
		return true;
	}

	/**
	 * Returns a guest user profile.
	 *
	 * @return 	UserProfile
	 */
	public static function getGuestUserProfile() {
		if (self::$userProfile === null) {
			self::$userProfile = UserProfile::getGuestUserProfile('');
		}

		return self::$userProfile;
	}
}
