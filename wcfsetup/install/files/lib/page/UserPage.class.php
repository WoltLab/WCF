<?php

namespace wcf\page;

use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\cover\photo\UserCoverPhoto;
use wcf\data\user\follow\UserFollowerList;
use wcf\data\user\follow\UserFollowingList;
use wcf\data\user\group\UserGroup;
use wcf\data\user\profile\visitor\UserProfileVisitorAction;
use wcf\data\user\profile\visitor\UserProfileVisitorList;
use wcf\data\user\UserEditor;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\menu\user\profile\UserProfileMenu;
use wcf\system\MetaTagHandler;
use wcf\system\page\PageLocationManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the user profile page.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserPage extends AbstractPage
{
    /**
     * edit profile on page load
     * @var bool
     */
    public $editOnInit = false;

    /**
     * overview editable content object type
     * @var ObjectType
     */
    public $objectType;

    /**
     * profile content for active menu item
     * @var string
     */
    public $profileContent = '';

    /**
     * user id
     * @var int
     */
    public $userID = 0;

    /**
     * user object
     * @var UserProfile
     */
    public $user;

    /**
     * follower list
     * @var UserFollowerList
     */
    public $followerList;

    /**
     * following list
     * @var UserFollowingList
     */
    public $followingList;

    /**
     * visitor list
     * @var UserProfileVisitorList
     */
    public $visitorList;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->userID = \intval($_REQUEST['id']);
        }
        $this->user = UserProfileRuntimeCache::getInstance()->getObject($this->userID);
        if ($this->user === null) {
            throw new IllegalLinkException();
        }

        if ($this->user->userID != WCF::getUser()->userID && !WCF::getSession()->getPermission('user.profile.canViewUserProfile')) {
            throw new PermissionDeniedException();
        }

        if (isset($_REQUEST['editOnInit'])) {
            $this->editOnInit = true;
        }

        $this->canonicalURL = $this->user->getLink();
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // add breadcrumbs
        if (MODULE_MEMBERS_LIST) {
            PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.MembersList');
        }

        // get profile content
        if ($this->editOnInit) {
            // force 'about' tab as primary if editing profile
            UserProfileMenu::getInstance()->setActiveMenuItem('about');
        }

        $activeMenuItem = UserProfileMenu::getInstance()->getActiveMenuItem($this->user->userID);
        $contentManager = $activeMenuItem->getContentManager();
        $this->profileContent = $contentManager->getContent($this->user->userID);
        $this->objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.user.profileEditableContent',
            'com.woltlab.wcf.user.profileAbout'
        );

        // get followers
        $this->followerList = new UserFollowerList();
        $this->followerList->getConditionBuilder()->add('user_follow.followUserID = ?', [$this->userID]);
        $this->followerList->sqlLimit = 7;
        $this->followerList->readObjects();

        // get following
        $this->followingList = new UserFollowingList();
        $this->followingList->getConditionBuilder()->add('user_follow.userID = ?', [$this->userID]);
        $this->followingList->sqlLimit = 7;
        $this->followingList->readObjects();

        // get visitors
        if (PROFILE_ENABLE_VISITORS) {
            $this->visitorList = new UserProfileVisitorList();
            $this->visitorList->getConditionBuilder()->add('user_profile_visitor.ownerID = ?', [$this->userID]);
            $this->visitorList->sqlLimit = 7;
            $this->visitorList->readObjects();
        }

        MetaTagHandler::getInstance()->addTag(
            'og:url',
            'og:url',
            LinkHandler::getInstance()->getLink('User', ['object' => $this->user->getDecoratedObject()]),
            true
        );
        MetaTagHandler::getInstance()->addTag('og:type', 'og:type', 'profile', true);
        MetaTagHandler::getInstance()->addTag('profile:username', 'profile:username', $this->user->username, true);
        MetaTagHandler::getInstance()->addTag(
            'og:title',
            'og:title',
            $this->user->username . ' - ' . WCF::getLanguage()->get('wcf.user.members') . ' - ' . WCF::getLanguage()->get(PAGE_TITLE),
            true
        );
        MetaTagHandler::getInstance()->addTag('og:image', 'og:image', $this->user->getAvatar()->getURL(), true);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'editOnInit' => $this->editOnInit,
            'overviewObjectType' => $this->objectType,
            'profileContent' => $this->profileContent,
            'userID' => $this->userID,
            'user' => $this->user,
            'followers' => $this->followerList->getObjects(),
            'followerCount' => $this->followerList->countObjects(),
            'following' => $this->followingList->getObjects(),
            'followingCount' => $this->followingList->countObjects(),
            'visitors' => $this->visitorList !== null ? $this->visitorList->getObjects() : [],
            'visitorCount' => $this->visitorList !== null ? $this->visitorList->countObjects() : 0,
            'isAccessible' => UserGroup::isAccessibleGroup($this->user->getGroupIDs()),
            'coverPhotoDimensions' => UserCoverPhoto::getCoverPhotoDimensions(),
            'specialTrophyCount' => (MODULE_TROPHY ? \count($this->user->getSpecialTrophies()) : 0),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function show()
    {
        // update profile hits
        if ($this->user->userID != WCF::getUser()->userID && !WCF::getSession()->spiderIdentifier && !$this->user->isProtected()) {
            $editor = new UserEditor($this->user->getDecoratedObject());
            $editor->updateCounters(['profileHits' => 1]);

            // save visitor
            /** @noinspection PhpUndefinedFieldInspection */
            if (PROFILE_ENABLE_VISITORS && WCF::getUser()->userID && !WCF::getUser()->canViewOnlineStatus) {
                (new UserProfileVisitorAction([], 'registerVisitor', [
                    'data' => [
                        'ownerID' => $this->user->userID,
                        'userID' => WCF::getUser()->userID,
                    ],
                ]))->executeAction();
            }
        }

        parent::show();
    }
}
