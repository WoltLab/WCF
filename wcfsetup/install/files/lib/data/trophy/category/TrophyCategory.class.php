<?php

namespace wcf\data\trophy\category;

use wcf\data\category\AbstractDecoratedCategory;
use wcf\data\ITitledLinkObject;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyCache;
use wcf\data\user\User;
use wcf\page\CategoryTrophyListPage;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents a trophy category.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TrophyCategory extends AbstractDecoratedCategory implements ITitledLinkObject
{
    /**
     * object type name of the trophy categories
     * @var string
     */
    const OBJECT_TYPE_NAME = 'com.woltlab.wcf.trophy.category';

    /**
     * @return bool
     */
    public function isAccessible(?User $user = null)
    {
        if ($this->getObjectType()->objectType !== self::OBJECT_TYPE_NAME) {
            return false;
        }

        if ($this->getDecoratedObject()->isDisabled !== 0) {
            return false;
        }

        return true;
    }

    #[\Override]
    public function getLink(): string
    {
        return LinkHandler::getInstance()->getControllerLink(CategoryTrophyListPage::class, [
            'object' => $this->getDecoratedObject(),
        ]);
    }

    #[\Override]
    public function getTitle(): string
    {
        return WCF::getLanguage()->get($this->title);
    }

    /**
     * Returns the trophies for the category.
     *
     * @return  Trophy[]
     */
    public function getTrophies(bool $includeDisabled = false)
    {
        if ($includeDisabled) {
            return TrophyCache::getInstance()->getTrophiesByCategoryID($this->getObjectID());
        }

        return TrophyCache::getInstance()->getEnabledTrophiesByCategoryID($this->getObjectID());
    }
}
