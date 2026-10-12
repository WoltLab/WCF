<?php

namespace wcf\data\user;

use wcf\command\file\DeleteFiles;
use wcf\command\user\AddUserToGroups;
use wcf\command\user\BanUser;
use wcf\command\user\ConfirmUserEmail;
use wcf\command\user\DisableUser;
use wcf\command\user\EnableUser;
use wcf\command\user\RefreshUserOnlineMarkings;
use wcf\command\user\RefreshUserRanks;
use wcf\command\user\RemoveUserFromGroups;
use wcf\command\user\ResendActivationMail;
use wcf\command\user\UnbanUser;
use wcf\command\user\UnconfirmUserEmail;
use wcf\command\user\UpdateUser;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\file\File;
use wcf\data\ISearchAction;
use wcf\data\user\group\UserGroup;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\comment\CommentHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\session\SessionHandler;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\WCF;

/**
 * Executes user-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<User, UserEditor>
 */
class UserAction extends AbstractDatabaseObjectAction implements ISearchAction
{
    /**
     * @inheritDoc
     */
    public $className = UserEditor::class;

    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['getSearchResultList'];

    /**
     * @inheritDoc
     */
    protected $permissionsCreate = ['admin.user.canAddUser'];

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.user.canDeleteUser'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.user.canEditUser'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['create', 'delete', 'resendActivationMail'];

    /**
     * Validates permissions and parameters.
     *
     * @return void
     */
    #[\Override]
    public function validateCreate()
    {
        // This method has been unused for over a decade. We cannot remove its
        // definition because it would be implicitly enabled due to the
        // `$permissionsUpdate` property which we cannot nullify to preserve
        // the backwards-compatibility.
        throw new PermissionDeniedException();
    }

    /**
     * Validates accessible groups.
     *
     * @return void
     * @throws  PermissionDeniedException
     * @throws  UserInputException
     */
    protected function __validateAccessibleGroups(bool $ignoreOwnUser = true)
    {
        if ($ignoreOwnUser) {
            if (\in_array(WCF::getUser()->userID, $this->objectIDs, true)) {
                unset($this->objectIDs[\array_search(WCF::getUser()->userID, $this->objectIDs, true)]);
                if (isset($this->objects[WCF::getUser()->userID])) {
                    unset($this->objects[WCF::getUser()->userID]);
                }
            }
        }

        // list might be empty because only our own user id was given
        if ($this->objectIDs === []) {
            throw new UserInputException('objectIDs');
        }

        // validate groups
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("userID IN (?)", [$this->objectIDs]);

        $sql = "SELECT  DISTINCT groupID
                FROM    wcf1_user_to_group
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $groupIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        if (!UserGroup::isAccessibleGroup($groupIDs)) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Validates permissions and parameters.
     *
     * @deprecated 6.3
     */
    #[\Override]
    public function validateDelete()
    {
        // read and validate user objects
        parent::validateDelete();

        $this->__validateAccessibleGroups();
    }

    /**
     * @deprecated 6.3 Use the `DeleteUser` command instead.
     */
    #[\Override]
    public function delete()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        // delete avatars
        $avatarFileIDs = [];
        foreach ($this->getObjects() as $user) {
            if ($user->avatarFileID !== null) {
                $avatarFileIDs[] = $user->avatarFileID;
            }
        }
        $avatarFiles = File::findByIDs($avatarFileIDs);
        if ($avatarFiles !== []) {
            new DeleteFiles($avatarFiles)();
        }

        // delete profile comments and signature attachments
        if ($this->objectIDs !== []) {
            CommentHandler::getInstance()->deleteObjects('com.woltlab.wcf.user.profileComment', $this->objectIDs);
            AttachmentHandler::removeAttachments('com.woltlab.wcf.user.signature', $this->objectIDs);
        }

        return parent::delete();
    }

    /**
     * Validates permissions and parameters.
     *
     * @return void
     */
    #[\Override]
    public function validateUpdate()
    {
        // This method has been unused for over a decade. We cannot remove its
        // definition because it would be implicitly enabled due to the
        // `$permissionsUpdate` property which we cannot nullify to preserve
        // the backwards-compatibility.
        throw new PermissionDeniedException();
    }

    /**
     * Validates the ban action.
     *
     * @return void
     *
     * @deprecated 6.3
     */
    public function validateBan()
    {
        $this->validateUnban();

        $this->readString('banReason', true);
        $this->readString('banExpires', true);
    }

    /**
     * Validates the unban action.
     *
     * @return void
     *
     * @deprecated 6.3
     */
    public function validateUnban()
    {
        WCF::getSession()->checkPermissions(['admin.user.canBanUser']);

        $this->__validateAccessibleGroups();
    }

    /**
     * Bans users.
     *
     * @return string
     *
     * @deprecated 6.3 Use the `BanUser` command instead.
     */
    public function ban()
    {
        $banExpires = $this->parameters['banExpires'];
        if ($banExpires !== '') {
            $banExpires = \DateTimeImmutable::createFromFormat('!Y-m-d', $banExpires, new \DateTimeZone(\TIMEZONE))->getTimestamp();
            if ($banExpires > 2147483647) {
                $banExpires = 2147483647;
            }
        } else {
            $banExpires = 0;
        }

        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $userEditor) {
            (new BanUser($userEditor->getDecoratedObject(), $this->parameters['banReason'], $banExpires))();
        }

        $firstUser = new User(\reset($this->objectIDs));

        return WCF::getLanguage()->getDynamicVariable('wcf.user.banned', ['user' => $firstUser]);
    }

    /**
     * Unbans users.
     *
     * @return void
     *
     * @deprecated 6.3 Use the `UnbanUser` command instead.
     */
    public function unban()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $userEditor) {
            (new UnbanUser($userEditor->getDecoratedObject()))();
        }
    }

    #[\Override]
    public function create()
    {
        /** @var User $user */
        $user = parent::create();
        $userEditor = new UserEditor($user);

        // updates user options
        if (isset($this->parameters['options'])) {
            $userEditor->updateUserOptions($this->parameters['options']);
        }

        // insert user groups
        $addDefaultGroups = $this->parameters['addDefaultGroups'] ?? true;
        $groupIDs = $this->parameters['groups'] ?? [];
        $userEditor->addToGroups($groupIDs, false, $addDefaultGroups);

        // insert visible languages
        if (!isset($this->parameters['languageIDs'])) {
            // using the 'languages' key is deprecated since WCF 2.1, please use 'languageIDs' instead
            $this->parameters['languageIDs'] = (!empty($this->parameters['languages'])) ? $this->parameters['languages'] : [];
        }
        $userEditor->addToLanguages($this->parameters['languageIDs'], false);

        if (\PACKAGE_ID !== 0) {
            // set default notifications
            $sql = "INSERT INTO wcf1_user_notification_event_to_user
                                (userID, eventID, mailNotificationType)
                    SELECT      ?, eventID, presetMailNotificationType
                    FROM        wcf1_user_notification_event
                    WHERE       preset = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$user->userID, 1]);

            // update user rank
            if (\MODULE_USER_RANK !== 0) {
                (new RefreshUserRanks([$user]))();
            }
            // update user online marking
            (new RefreshUserOnlineMarkings([$user]))();
        }

        return $user;
    }

    #[\Override]
    public function update()
    {
        if (isset($this->parameters['data']) || isset($this->parameters['counters'])) {
            parent::update();

            if (isset($this->parameters['data']['languageID'])) {
                foreach ($this->getObjects() as $object) {
                    if ($object->userID === WCF::getUser()->userID) {
                        if ($this->parameters['data']['languageID'] !== WCF::getUser()->languageID) {
                            WCF::setLanguage($this->parameters['data']['languageID']);
                        }

                        break;
                    }
                }
            }

            if (
                isset($this->parameters['data'])
                && \array_key_exists('password', $this->parameters['data'])
                && $this->parameters['data']['password'] !== ''
            ) {
                foreach ($this->getObjects() as $object) {
                    SessionHandler::getInstance()->deleteUserSessionsExcept(
                        $object->getDecoratedObject(),
                        SessionHandler::getInstance()->sessionID
                    );
                }
            }
        } else {
            if ($this->objects === []) {
                $this->readObjects();
            }
        }

        $groupIDs = $this->parameters['groups'] ?? [];
        $languageIDs = $this->parameters['languageIDs'] ?? [];
        $removeGroups = $this->parameters['removeGroups'] ?? [];
        $userOptions = $this->parameters['options'] ?? [];

        if (!empty($groupIDs)) {
            $action = new self($this->objects, 'addToGroups', [
                'groups' => $groupIDs,
                'addDefaultGroups' => false,
            ]);
            $action->executeAction();
        }

        if (!empty($removeGroups)) {
            $action = new self($this->objects, 'removeFromGroups', [
                'groups' => $removeGroups,
            ]);
            $action->executeAction();
        }

        foreach ($this->getObjects() as $userEditor) {
            if (!empty($userOptions)) {
                $userEditor->updateUserOptions($userOptions);
            }

            if (!empty($languageIDs)) {
                $userEditor->addToLanguages($languageIDs);
            }
        }

        // handle user rename
        if (\count($this->objects) === 1 && !empty($this->parameters['data']['username'])) {
            if ($this->objects[0]->username !== $this->parameters['data']['username']) {
                $userID = $this->objects[0]->userID;
                $username = $this->parameters['data']['username'];

                WCF::getDB()->beginTransaction();

                // update article
                $sql = "UPDATE  wcf1_article
                        SET     username = ?
                        WHERE   userID = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([$username, $userID]);

                // update comments
                $sql = "UPDATE  wcf1_comment
                        SET     username = ?
                        WHERE   userID = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([$username, $userID]);

                // update comment responses
                $sql = "UPDATE  wcf1_comment_response
                        SET     username = ?
                        WHERE   userID = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([$username, $userID]);

                // update media
                $sql = "UPDATE  wcf1_media
                        SET     username = ?
                        WHERE   userID = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([$username, $userID]);

                // update modification log
                $sql = "UPDATE  wcf1_modification_log
                        SET     username = ?
                        WHERE   userID = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([$username, $userID]);

                WCF::getDB()->commitTransaction();

                // fire event to handle other database tables
                EventHandler::getInstance()->fireAction($this, 'rename');
            }
        }
    }

    /**
     * Remove users from given groups.
     *
     * @return void
     *
     * @deprecated 6.3 Use the `RemoveUserFromGroups` command instead.
     */
    public function removeFromGroups()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $userEditor) {
            (new RemoveUserFromGroups(
                $userEditor->getDecoratedObject(),
                \array_values(\array_map(static fn($groupID) => (int)$groupID, $this->parameters['groups']))
            ))();
        }
    }

    /**
     * Add users to given groups.
     *
     * @return void
     *
     * @deprecated 6.3 Use the `AddUserToGroups` command instead.
     */
    public function addToGroups()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        $deleteOldGroups = (bool)($this->parameters['deleteOldGroups'] ?? true);
        $addDefaultGroups = (bool)($this->parameters['addDefaultGroups'] ?? true);
        $ignoreUserGroupAssignments = (bool)($this->parameters['ignoreUserGroupAssignments'] ?? false);

        $groupIDs = \array_map(static fn($groupID) => (int)$groupID, $this->parameters['groups']);
        if ($addDefaultGroups) {
            $groupIDs = \array_merge($groupIDs, UserGroup::getGroupIDsByType([UserGroup::EVERYONE, UserGroup::USERS]));
        }
        $groupIDs = \array_values(\array_unique($groupIDs));

        foreach ($this->getObjects() as $userEditor) {
            if ($deleteOldGroups) {
                (new UpdateUser(
                    UserBuilder::forUpdate($userEditor->getDecoratedObject())->setGroupIDs($groupIDs),
                    $ignoreUserGroupAssignments
                ))();
            } else {
                (new AddUserToGroups(
                    $userEditor->getDecoratedObject(),
                    $groupIDs,
                    $ignoreUserGroupAssignments
                ))();
            }
        }
    }

    #[\Override]
    public function validateGetSearchResultList()
    {
        $this->readBoolean('includeUserGroups', false, 'data');
        $this->readString('searchString', false, 'data');
        $this->readIntegerArray('restrictUserGroupIDs', true, 'data');
        $this->readString('scope', true, 'data');

        if (isset($this->parameters['data']['excludedSearchValues']) && !\is_array($this->parameters['data']['excludedSearchValues'])) {
            throw new UserInputException('excludedSearchValues');
        }

        if ($this->parameters['data']['scope'] !== '') {
            if (!\in_array($this->parameters['data']['scope'], ['mention'], true)) {
                throw new UserInputException('scope');
            }
        }
    }

    #[\Override]
    public function getSearchResultList()
    {
        $searchString = $this->parameters['data']['searchString'];
        $excludedSearchValues = [];
        if (isset($this->parameters['data']['excludedSearchValues'])) {
            $excludedSearchValues = $this->parameters['data']['excludedSearchValues'];
        }
        $list = [];

        if ($this->parameters['data']['includeUserGroups'] === true) {
            if ($this->parameters['data']['scope'] === 'mention') {
                $accessibleGroups = UserGroup::getMentionableGroups();
            } else {
                $accessibleGroups = UserGroup::getAllGroups();
            }

            foreach ($accessibleGroups as $group) {
                if (
                    !empty($this->parameters['data']['restrictUserGroupIDs']) && !\in_array(
                        $group->groupID,
                        $this->parameters['data']['restrictUserGroupIDs'],
                        true
                    )
                ) {
                    continue;
                }

                if ($this->parameters['data']['scope'] === 'mention' && (!WCF::getSession()->hasPermission('user.message.canMentionGroups') || !$group->canBeMentioned())) {
                    continue;
                }

                $groupName = $group->getName();
                if (!\in_array($groupName, $excludedSearchValues, true)) {
                    $pos = \mb_strripos($groupName, $searchString);
                    if ($pos !== false && $pos === 0) {
                        $list[] = [
                            'icon' => FontAwesomeIcon::fromValues('users')->toHtml(),
                            'label' => $groupName,
                            'objectID' => $group->groupID,
                            'type' => 'group',
                        ];
                    }
                }
            }

            $collator = new \Collator(WCF::getLanguage()->getLocale());
            \usort(
                $list,
                static fn(array $item1, array $item2) => $collator->compare($item1['label'], $item2['label'])
            );
        }

        if (
            $this->parameters['data']['scope'] === 'mention'
            && !WCF::getSession()->hasPermission('user.message.canMention')
        ) {
            return $list;
        }

        // find users
        $searchString = WCF::getDB()->escapeLikeValue($searchString);
        $parameters = [
            'searchString' => $searchString,
        ];
        EventHandler::getInstance()->fireAction($this, 'beforeFindUsers', $parameters);
        $userProfileList = new UserProfileList();
        $userProfileList->getConditionBuilder()->add("username LIKE ?", [$parameters['searchString'] . '%']);
        if (!empty($excludedSearchValues)) {
            $userProfileList->getConditionBuilder()->add("username NOT IN (?)", [$excludedSearchValues]);
        }
        $userProfileList->sqlLimit = 10;
        $userProfileList->readObjects();

        foreach ($userProfileList as $userProfile) {
            $list[] = [
                'icon' => $userProfile->getAvatar()->getImageTag(16),
                'label' => $userProfile->username,
                'objectID' => $userProfile->userID,
                'type' => 'user',
            ];
        }

        return $list;
    }

    /**
     * @return void
     *
     * @deprecated 6.3
     */
    public function validateUnmarkAll()
    {
        // does nothing
    }

    /**
     * @return void
     *
     * @deprecated 6.3
     */
    public function unmarkAll()
    {
        // does nothing
    }

    /**
     * Unmarks users.
     *
     * @param int[] $userIDs
     * @return void
     *
     * @deprecated 6.3
     */
    protected function unmarkItems(array $userIDs = [])
    {
        // does nothing
    }

    /**
     * Validates the enable action.
     *
     * @return void
     *
     * @deprecated 6.3
     */
    public function validateEnable()
    {
        WCF::getSession()->checkPermissions(['admin.user.canEnableUser']);

        $this->__validateAccessibleGroups();
    }

    /**
     * Validates the disable action.
     *
     * @return void
     *
     * @deprecated 6.3
     */
    public function validateDisable()
    {
        $this->validateEnable();
    }

    /**
     * Validates the confirm email action.
     *
     * @return void
     * @since       5.3
     *
     * @deprecated 6.3
     */
    public function validateConfirmEmail()
    {
        $this->validateEnable();
    }

    /**
     * Validates the unconfirm email action.
     *
     * @return void
     * @since       5.3
     *
     * @deprecated 6.3
     */
    public function validateUnconfirmEmail()
    {
        $this->validateEnable();
    }

    /**
     * Marks the email address as confirmed. It also enables the user, if the register method is user activation only.
     *
     * @return void
     * @since       5.3
     *
     * @deprecated 6.3 Use the `ConfirmUserEmail` command instead.
     */
    public function confirmEmail()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        $sendNotification = !(bool)($this->parameters['skipNotification'] ?? false);
        foreach ($this->getObjects() as $userEditor) {
            (new ConfirmUserEmail($userEditor->getDecoratedObject(), $sendNotification))();
        }
    }

    /**
     * Marks the email address as unconfirmed.
     *
     * @return void
     * @since       5.3
     *
     * @deprecated 6.3 Use the `UnconfirmUserEmail` command instead.
     */
    public function unconfirmEmail()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $userEditor) {
            (new UnconfirmUserEmail($userEditor->getDecoratedObject()))();
        }
    }

    /**
     * Enables users.
     *
     * @return void
     *
     * @deprecated 6.3 Use the `EnableUser` command instead.
     */
    public function enable()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        $sendNotification = !(bool)($this->parameters['skipNotification'] ?? false);
        foreach ($this->getObjects() as $userEditor) {
            (new EnableUser($userEditor->getDecoratedObject(), $sendNotification))();
        }
    }

    /**
     * Disables users.
     *
     * @return void
     *
     * @deprecated 6.3 Use the `DisableUser` command instead.
     */
    public function disable()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $userEditor) {
            (new DisableUser($userEditor->getDecoratedObject()))();
        }
    }

    #[\Override]
    protected function readObjects()
    {
        if ($this->objectIDs === []) {
            return;
        }

        // get base class
        $baseClass = \call_user_func([$this->className, 'getBaseClass']);

        // get objects
        $sql = "SELECT      user_option_value.*, user_table.*
                FROM        wcf1_user user_table
                LEFT JOIN   wcf1_user_option_value user_option_value
                ON          user_option_value.userID = user_table.userID
                WHERE       user_table.userID IN (" . \str_repeat('?,', \count($this->objectIDs) - 1) . "?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($this->objectIDs);
        // @phpstan-ignore argument.templateType
        while ($object = $statement->fetchObject($baseClass)) {
            $this->objects[] = new $this->className($object);
        }
    }

    /**
     * Validates the 'disableSignature' action.
     *
     * @return void
     *
     * @deprecated 6.3
     */
    public function validateDisableSignature()
    {
        $this->validateEnableSignature();

        $this->readString('disableSignatureReason', true);
        $this->readString('disableSignatureExpires', true);
    }

    /**
     * Disables the signature of the handled users.
     *
     * @return void
     *
     * @deprecated 6.3 Use the `DisableUserSignature` command instead.
     */
    public function disableSignature()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        $disableSignatureExpires = $this->parameters['disableSignatureExpires'];
        if ($disableSignatureExpires !== '') {
            $disableSignatureExpires = \strtotime($disableSignatureExpires);
        } else {
            $disableSignatureExpires = 0;
        }

        foreach ($this->getObjects() as $userEditor) {
            $userEditor->update([
                'disableSignature' => 1,
                'disableSignatureReason' => $this->parameters['disableSignatureReason'],
                'disableSignatureExpires' => $disableSignatureExpires,
            ]);
        }
    }

    /**
     * Validates the 'enableSignature' action.
     *
     * @return void
     *
     * @deprecated 6.3
     */
    public function validateEnableSignature()
    {
        WCF::getSession()->checkPermissions(['admin.user.canDisableSignature']);

        $this->__validateAccessibleGroups();

        if ($this->objects === []) {
            $this->readObjects();

            if ($this->objects === []) {
                throw new UserInputException('objectIDs');
            }
        }
    }

    /**
     * Enables the signature of the handled users.
     *
     * @return void
     *
     * @deprecated 6.3 Use the `EnableUserSignature` command instead.
     */
    public function enableSignature()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $userEditor) {
            $userEditor->update([
                'disableSignature' => 0,
            ]);
        }
    }

    /**
     * Validates the 'disableAvatar' action.
     *
     * @return void
     *
     * @deprecated 6.3
     */
    public function validateDisableAvatar()
    {
        $this->validateEnableAvatar();

        $this->readString('disableAvatarReason', true);
        $this->readString('disableAvatarExpires', true);
    }

    /**
     * Disables the avatar of the handled users.
     *
     * @return void
     *
     * @deprecated 6.3 Use the `DisableUserAvatar` command instead.
     */
    public function disableAvatar()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        $disableAvatarExpires = $this->parameters['disableAvatarExpires'];
        if ($disableAvatarExpires !== '') {
            $disableAvatarExpires = \strtotime($disableAvatarExpires);
        } else {
            $disableAvatarExpires = 0;
        }

        foreach ($this->getObjects() as $userEditor) {
            $userEditor->update([
                'disableAvatar' => 1,
                'disableAvatarReason' => $this->parameters['disableAvatarReason'],
                'disableAvatarExpires' => $disableAvatarExpires,
            ]);
        }
    }

    /**
     * Validates the 'disableCoverPhoto' action.
     *
     * @return void
     * @since   5.2
     *
     * @deprecated 6.3
     */
    public function validateDisableCoverPhoto()
    {
        $this->validateEnableCoverPhoto();

        $this->readString('disableCoverPhotoReason', true);
        $this->readString('disableCoverPhotoExpires', true);
    }

    /**
     * Disables the cover photo of the handled users.
     *
     * @return void
     * @since   5.2
     *
     * @deprecated 6.3 Use the `DisableUserCoverPhoto` command instead.
     */
    public function disableCoverPhoto()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        $disableCoverPhotoExpires = $this->parameters['disableCoverPhotoExpires'];
        if ($disableCoverPhotoExpires !== '') {
            $disableCoverPhotoExpires = \strtotime($disableCoverPhotoExpires);
        } else {
            $disableCoverPhotoExpires = 0;
        }

        foreach ($this->getObjects() as $userEditor) {
            $userEditor->update([
                'disableCoverPhoto' => 1,
                'disableCoverPhotoReason' => $this->parameters['disableCoverPhotoReason'],
                'disableCoverPhotoExpires' => $disableCoverPhotoExpires,
            ]);
        }
    }

    /**
     * Validates the 'enableAvatar' action.
     *
     * @return void
     *
     * @deprecated 6.3
     */
    public function validateEnableAvatar()
    {
        WCF::getSession()->checkPermissions(['admin.user.canDisableAvatar']);

        $this->__validateAccessibleGroups();

        if ($this->objects === []) {
            $this->readObjects();

            if ($this->objects === []) {
                throw new UserInputException('objectIDs');
            }
        }
    }

    /**
     * Enables the avatar of the handled users.
     *
     * @return void
     *
     * @deprecated 6.3 Use the `EnableUserAvatar` command instead.
     */
    public function enableAvatar()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $userEditor) {
            $userEditor->update([
                'disableAvatar' => 0,
            ]);
        }
    }

    /**
     * Validates the 'enableCoverPhoto' action.
     *
     * @return void
     * @since   5.2
     *
     * @deprecated 6.3
     */
    public function validateEnableCoverPhoto()
    {
        WCF::getSession()->checkPermissions(['admin.user.canDisableCoverPhoto']);

        $this->__validateAccessibleGroups();

        if ($this->objects === []) {
            $this->readObjects();

            if ($this->objects === []) {
                throw new UserInputException('objectIDs');
            }
        }
    }

    /**
     * Enables the cover photo of the handled users.
     *
     * @return void
     * @since   5.2
     *
     * @deprecated 6.3 Use the `EnableUserCoverPhoto` command instead.
     */
    public function enableCoverPhoto()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $userEditor) {
            $userEditor->update([
                'disableCoverPhoto' => 0,
            ]);
        }
    }

    /**
     * @return void
     * @since 5.3
     * @deprecated 6.3
     */
    public function validateSaveUserConsent() {}

    /**
     * @return void
     * @since 5.3
     * @deprecated 6.3 Use the `wcf\system\endpoint\controller\core\users\SaveUserConsent` endpoint instead.
     */
    public function saveUserConsent()
    {
        $userEditor = new UserEditor(WCF::getUser());
        $userEditor->updateUserOptions([
            User::getUserOptionID('enableEmbeddedMedia') => 1,
        ]);
    }

    /**
     * Validates the 'resendActivationMail' action.
     *
     * @return void
     * @throws  IllegalLinkException
     * @throws  PermissionDeniedException
     * @throws  UserInputException
     * @since   5.2
     *
     * @deprecated 6.3
     */
    public function validateResendActivationMail()
    {
        $this->readObjects();

        if (!WCF::getSession()->hasPermission('admin.user.canEnableUser')) {
            throw new PermissionDeniedException();
        }

        if (((int)\REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_USER) === 0) {
            throw new IllegalLinkException();
        }

        foreach ($this->objects as $object) {
            if (!$object->canEmailConfirm()) {
                throw new UserInputException('objectIDs');
            }
        }
    }

    /**
     * Triggers a new activation email.
     *
     * @return void
     * @since   5.2
     *
     * @deprecated 6.3 Use the `ResendActivationMail` command instead.
     */
    public function resendActivationMail()
    {
        foreach ($this->getObjects() as $userEditor) {
            (new ResendActivationMail($userEditor->getDecoratedObject()))();
        }
    }

    /**
     * @return void
     * @since   5.2
     * @deprecated 6.3
     */
    public function validateDevtoolsSetLanguage()
    {
        if (\ENABLE_DEBUG_MODE === 0 || \ENABLE_DEVELOPER_TOOLS === 0) {
            throw new PermissionDeniedException();
        }

        $this->readInteger('languageID');

        if (LanguageFactory::getInstance()->getLanguage($this->parameters['languageID']) === null) {
            throw new UserInputException('languageID', 'invalid');
        }
    }

    /**
     * @return void
     * @since   5.2
     * @deprecated 6.3 Use the `wcf\system\endpoint\controller\core\devtools\SetLanguage` endpoint instead.
     */
    public function devtoolsSetLanguage()
    {
        (new UserEditor(WCF::getUser()))->update([
            'languageID' => $this->parameters['languageID'],
        ]);
    }

    /**
     * @since   5.5
     * @deprecated 6.3
     */
    public function cancelLostPasswordRequest(): void
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $userEditor) {
            $userEditor->update([
                'lastLostPasswordRequestTime' => 0,
                'lostPasswordKey' => '',
            ]);
        }
    }
}
