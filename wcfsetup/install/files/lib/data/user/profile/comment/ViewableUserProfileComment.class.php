<?php
declare(strict_types=1);
namespace wcf\data\user\profile\comment;
use wcf\data\comment\Comment;
use wcf\data\comment\ViewableComment;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\WCF;

/**
 * Represents a viewable user profile comment.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Data\Comment
 *
 * @method      Comment         getDecoratedObject()
 * @mixin       Comment
 */
class ViewableUserProfileComment extends ViewableComment {
	/**
	 * @inheritDoc
	 */
	public function __get($name) {
		if ($name === 'title') {
			return WCF::getLanguage()->getDynamicVariable('wcf.user.profile.title', ['user' => UserProfileRuntimeCache::getInstance()->getObject($this->objectID)]);
		}
		
		return parent::__get($name);
	}
}
