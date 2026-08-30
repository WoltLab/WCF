<?php

namespace wcf\data\user\profile\comment;

use wcf\data\comment\Comment;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\WCF;

/**
 * Represents a viewable user profile comment.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   Comment
 * @extends DatabaseObjectDecorator<Comment>
 */
class ViewableUserProfileComment extends DatabaseObjectDecorator
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Comment::class;

    #[\Override]
    public function __get(string $name)
    {
        if ($name === 'title') {
            return WCF::getLanguage()->getDynamicVariable(
                'wcf.user.profile.title',
                ['user' => UserProfileRuntimeCache::getInstance()->getObject($this->objectID)]
            );
        }

        return parent::__get($name);
    }
}
