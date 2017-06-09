<?php
namespace wcf\system\sitemap\object;
use wcf\data\user\User;
use wcf\data\DatabaseObject;

/**
 * User sitemap implementation.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Sitemap\Object
 * @since	3.1
 */
class UserSitemapObject extends AbstractSitemapObjectObjectType {
	/**
	 * @inheritDoc
	 */
	public function getObjectClass() {
		return User::class;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLastModifiedColumn() {
		return 'lastActivityTime';
	}
	
	/**
	 * @inheritDoc
	 */
	public function canView(DatabaseObject $object) {
		return self::getGuestUserProfile()->getPermission('user.profile.canViewUserProfile');
	}
}
