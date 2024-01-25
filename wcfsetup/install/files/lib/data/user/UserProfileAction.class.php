<?php

namespace wcf\data\user;

use wcf\data\IPopoverAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\avatar\UserAvatar;
use wcf\data\user\avatar\UserAvatarAction;
use wcf\data\user\avatar\UserAvatarEditor;
use wcf\data\user\group\UserGroup;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\option\user\UserOptionHandler;
use wcf\system\upload\UploadFile;
use wcf\system\upload\UploadHandler;
use wcf\system\upload\UserCoverPhotoUploadFileSaveStrategy;
use wcf\system\upload\UserCoverPhotoUploadFileValidationStrategy;
use wcf\system\user\group\assignment\UserGroupAssignmentHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\FileUtil;
use wcf\util\ImageUtil;
use wcf\util\MessageUtil;
use wcf\util\StringUtil;

/**
 * Executes user profile-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserProfileAction extends UserAction implements IPopoverAction
{
    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['getUserProfile', 'getDetailedActivityPointList', 'getPopover'];

    /**
     * @var User
     */
    public $user;

    /**
     * user profile object
     * @var UserProfile
     */
    public $userProfile;

    /**
     * uploaded file
     * @var UploadFile
     */
    public $uploadFile;

    /**
     * Validates parameters for signature preview.
     */
    public function validateGetMessagePreview()
    {
        $this->readString('message', true, 'data');
    }

    /**
     * Returns a rendered signature preview.
     *
     * @return  array
     * @throws  UserInputException
     */
    public function getMessagePreview()
    {
        $htmlInputProcessor = new HtmlInputProcessor();
        $htmlInputProcessor->process(
            $this->parameters['data']['message'],
            'com.woltlab.wcf.user.signature',
            WCF::getUser()->userID
        );

        BBCodeHandler::getInstance()->setDisallowedBBCodes(ArrayUtil::trim(\explode(
            ',',
            WCF::getSession()->getPermission('user.signature.disallowedBBCodes')
        )));
        $disallowedBBCodes = $htmlInputProcessor->validate();
        if (!empty($disallowedBBCodes)) {
            throw new UserInputException(
                'message',
                WCF::getLanguage()->getDynamicVariable('wcf.message.error.disallowedBBCodes', [
                    'disallowedBBCodes' => $disallowedBBCodes,
                ])
            );
        }

        MessageEmbeddedObjectManager::getInstance()->registerTemporaryMessage($htmlInputProcessor);

        $htmlOutputProcessor = new HtmlOutputProcessor();
        $htmlOutputProcessor->process(
            $htmlInputProcessor->getHtml(),
            'com.woltlab.wcf.user.signature',
            WCF::getUser()->userID
        );

        return [
            'message' => $htmlOutputProcessor->getHtml(),
            'raw' => $htmlInputProcessor->getHtml(),
        ];
    }

    /**
     * Validates user profile preview.
     *
     * @throws  UserInputException
     * @deprecated  since 5.3, use `validateGetPopover()`
     */
    public function validateGetUserProfile()
    {
        $this->validateGetPopover();
    }

    /**
     * Returns user profile preview.
     *
     * @return  array
     * @deprecated  since 5.3, use `getPopover()`
     */
    public function getUserProfile()
    {
        return \array_merge($this->getPopover(), [
            'userID' => \reset($this->objectIDs),
        ]);
    }

    /**
     * Validates detailed activity point list
     *
     * @throws  UserInputException
     */
    public function validateGetDetailedActivityPointList()
    {
        if (\count($this->objectIDs) != 1) {
            throw new UserInputException('objectIDs');
        }
        $this->userProfile = UserProfileRuntimeCache::getInstance()->getObject(\reset($this->objectIDs));

        if ($this->userProfile === null) {
            throw new UserInputException('objectIDs');
        }
    }

    /**
     * Returns detailed activity point list.
     *
     * @return  array
     */
    public function getDetailedActivityPointList()
    {
        $activityPointObjectTypes = [];
        foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.user.activityPointEvent') as $objectType) {
            $activityPointObjectTypes[$objectType->objectTypeID] = $objectType;
        }

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('userID = ?', [$this->userProfile->userID]);
        $conditionBuilder->add('objectTypeID IN (?)', [\array_keys($activityPointObjectTypes)]);

        $sql = "SELECT  objectTypeID, activityPoints, items
                FROM    wcf" . WCF_N . "_user_activity_point
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($conditionBuilder->getParameters());
        $entries = [];
        while ($row = $statement->fetchArray()) {
            $entries[] = [
                'activityPoints' => $row['activityPoints'],
                'items' => $row['items'],
                'objectType' => $activityPointObjectTypes[$row['objectTypeID']],
            ];
        }

        return [
            'template' => WCF::getTPL()->fetch(
                'detailedActivityPointList',
                'wcf',
                [
                    'entries' => $entries,
                    'user' => $this->userProfile,
                ]
            ),
            'userID' => $this->userProfile->userID,
        ];
    }

    /**
     * Validates parameters to begin profile inline editing.
     *
     * @throws  PermissionDeniedException
     * @throws  UserInputException
     */
    public function validateBeginEdit()
    {
        if (!empty($this->objectIDs) && \count($this->objectIDs) == 1) {
            $userID = \reset($this->objectIDs);
            $this->userProfile = UserProfileRuntimeCache::getInstance()->getObject($userID);
        }

        if ($this->userProfile === null || !$this->userProfile->userID) {
            throw new UserInputException('objectIDs');
        }

        if ($this->userProfile->userID != WCF::getUser()->userID) {
            if (!$this->userProfile->canEdit()) {
                throw new PermissionDeniedException();
            }
        } elseif (!$this->userProfile->canEditOwnProfile()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Begins profile inline editing.
     *
     * @return  array
     */
    public function beginEdit()
    {
        $optionTree = $this->getOptionHandler($this->userProfile->getDecoratedObject())->getOptionTree();
        WCF::getTPL()->assign([
            'errorType' => [],
            'optionTree' => $optionTree,
            '__userTitle' => $this->userProfile->userTitle,
        ]);

        return [
            'template' => WCF::getTPL()->fetch('userProfileAboutEditable'),
        ];
    }

    /**
     * Validates parameters to save changes to user profile.
     *
     * @throws  PermissionDeniedException
     */
    public function validateSave()
    {
        $this->validateBeginEdit();

        if (!isset($this->parameters['values']) || !\is_array($this->parameters['values'])) {
            $this->parameters['values'] = [];
        }

        if (
            isset($this->parameters['values']['__userTitle'])
            && !WCF::getSession()->getPermission('user.profile.canEditUserTitle')
        ) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Saves changes to user profile.
     *
     * @return  array
     */
    public function save()
    {
        $userTitle = null;
        if (isset($this->parameters['values']['__userTitle'])) {
            $userTitle = StringUtil::trim(MessageUtil::stripCrap($this->parameters['values']['__userTitle']));
            unset($this->parameters['values']['__userTitle']);
        }

        $optionHandler = $this->getOptionHandler($this->userProfile->getDecoratedObject());
        $optionHandler->readUserInput($this->parameters);

        $errors = $optionHandler->validate();

        // validate user title
        if ($userTitle !== null) {
            try {
                if (\mb_strlen($userTitle) > USER_TITLE_MAX_LENGTH) {
                    throw new UserInputException('__userTitle', 'tooLong');
                }
                if (!StringUtil::executeWordFilter($userTitle, USER_FORBIDDEN_TITLES)) {
                    throw new UserInputException('__userTitle', 'forbidden');
                }
            } catch (UserInputException $e) {
                $errors[$e->getField()] = $e->getType();
            }
        }

        // validation was successful
        if (empty($errors)) {
            $saveOptions = $optionHandler->save();
            $data = [
                'options' => $saveOptions,
            ];

            // save user title
            if ($userTitle !== null) {
                $data['data'] = [
                    'userTitle' => $userTitle,
                ];
            }

            $userAction = new UserAction([$this->userProfile->userID], 'update', $data);
            $userAction->executeAction();

            // check if the user will be automatically added to new
            // user groups because of the changed user options
            UserGroupAssignmentHandler::getInstance()->checkUsers([$this->userProfile->userID]);

            // reload user object to get updated data
            $user = new User($this->userProfile->userID);

            // update user rank
            if (MODULE_USER_RANK) {
                $action = new self([new UserEditor($user)], 'updateUserRank');
                $action->executeAction();
            }

            // reload option handler
            $optionHandler = $this->getOptionHandler($user, false);

            // return parsed template
            $options = $optionHandler->getOptionTree();
            WCF::getTPL()->assign([
                'options' => $options,
                'userID' => $this->userProfile->userID,
            ]);

            return [
                'success' => true,
                'template' => WCF::getTPL()->fetch('userProfileAbout'),
            ];
        } else {
            // validation failed
            WCF::getTPL()->assign([
                'errorType' => $errors,
                'optionTree' => $optionHandler->getOptionTree(),
                '__userTitle' => $userTitle !== null ? $userTitle : $this->userProfile->userTitle,
            ]);

            return [
                'success' => false,
                'template' => WCF::getTPL()->fetch('userProfileAboutEditable'),
            ];
        }
    }

    /**
     * Updates user ranks.
     */
    public function updateUserRank()
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        $resetUserIDs = $userToRank = [];
        foreach ($this->getObjects() as $user) {
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('user_rank.groupID IN (?)', [$user->getGroupIDs()]);
            $conditionBuilder->add('user_rank.requiredPoints <= ?', [$user->activityPoints]);
            if ($user->gender) {
                $conditionBuilder->add('user_rank.requiredGender IN (?)', [[0, $user->gender]]);
            } else {
                $conditionBuilder->add('user_rank.requiredGender = ?', [0]);
            }

            $sql = "SELECT      user_rank.rankID
                    FROM        wcf" . WCF_N . "_user_rank user_rank
                    LEFT JOIN   wcf" . WCF_N . "_user_group user_group
                    ON          user_group.groupID = user_rank.groupID
                    " . $conditionBuilder . "
                    ORDER BY    user_group.priority DESC, user_rank.requiredPoints DESC, user_rank.requiredGender DESC";
            $statement = WCF::getDB()->prepareStatement($sql, 1);
            $statement->execute($conditionBuilder->getParameters());
            $row = $statement->fetchArray();
            if ($row === false) {
                if ($user->rankID) {
                    $userToRank[$user->userID] = null;
                    $resetUserIDs[] = $user->userID;
                }
            } else {
                if ($row['rankID'] != $user->rankID) {
                    $userToRank[$user->userID] = $row['rankID'];
                    $resetUserIDs[] = $user->userID;
                }
            }
        }

        if (!empty($userToRank)) {
            $sql = "UPDATE  wcf" . WCF_N . "_user
                    SET     rankID = ?
                    WHERE   userID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);

            WCF::getDB()->beginTransaction();
            foreach ($userToRank as $userID => $rankID) {
                $statement->execute([
                    $rankID,
                    $userID,
                ]);
            }
            WCF::getDB()->commitTransaction();
        }

        if (!empty($resetUserIDs)) {
            UserStorageHandler::getInstance()->reset($resetUserIDs, 'userRank');
        }
    }

    /**
     * Updates user online markings.
     */
    public function updateUserOnlineMarking()
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        $fixUserGroupIDs = $userToGroup = [];
        $newGroupIDs = [];
        foreach ($this->getObjects() as $user) {
            $groupIDs = $user->getGroupIDs();
            if (!\in_array(UserGroup::EVERYONE, $groupIDs)) {
                $fixUserGroupIDs[$user->userID] = [UserGroup::EVERYONE];
                $groupIDs[] = UserGroup::EVERYONE;
            }
            if ($user->pendingActivation()) {
                if (!\in_array(UserGroup::GUESTS, $groupIDs)) {
                    if (!isset($fixUserGroupIDs[$user->userID])) {
                        $fixUserGroupIDs[$user->userID] = [];
                    }
                    $fixUserGroupIDs[$user->userID][] = UserGroup::GUESTS;
                    $groupIDs[] = UserGroup::GUESTS;
                }
            } else {
                if (!\in_array(UserGroup::USERS, $groupIDs)) {
                    if (!isset($fixUserGroupIDs[$user->userID])) {
                        $fixUserGroupIDs[$user->userID] = [];
                    }
                    $fixUserGroupIDs[$user->userID][] = UserGroup::USERS;
                    $groupIDs[] = UserGroup::USERS;
                }
            }
            $newGroupIDs[$user->userID] = $groupIDs;

            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('groupID IN (?)', [$groupIDs]);

            $sql = "SELECT      groupID
                    FROM        wcf" . WCF_N . "_user_group
                    " . $conditionBuilder . "
                    ORDER BY    priority DESC";
            $statement = WCF::getDB()->prepareStatement($sql, 1);
            $statement->execute($conditionBuilder->getParameters());
            $row = $statement->fetchArray();
            if ($row['groupID'] != $user->userOnlineGroupID) {
                $userToGroup[$user->userID] = $row['groupID'];
            }
        }

        // add users to missing default user groups
        if (!empty($fixUserGroupIDs)) {
            $sql = "INSERT INTO wcf" . WCF_N . "_user_to_group
                                (userID, groupID)
                    VALUES      (?, ?)";
            $statement = WCF::getDB()->prepareStatement($sql);

            WCF::getDB()->beginTransaction();
            foreach ($fixUserGroupIDs as $userID => $groupIDs) {
                foreach ($groupIDs as $groupID) {
                    $statement->execute([$userID, $groupID]);
                }

                UserStorageHandler::getInstance()->update($userID, 'groupIDs', \serialize($newGroupIDs[$userID]));
            }
            WCF::getDB()->commitTransaction();
        }

        if (!empty($userToGroup)) {
            $sql = "UPDATE  wcf" . WCF_N . "_user
                    SET     userOnlineGroupID = ?
                    WHERE   userID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);

            WCF::getDB()->beginTransaction();
            foreach ($userToGroup as $userID => $groupID) {
                $statement->execute([
                    $groupID,
                    $userID,
                ]);
            }
            WCF::getDB()->commitTransaction();
        }
    }

    /**
     * Updates the special trophies.
     */
    public function updateSpecialTrophies()
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        $sql = "DELETE FROM wcf" . WCF_N . "_user_special_trophy
                WHERE userID = ?";
        $deleteStatement = WCF::getDB()->prepareStatement($sql);

        $sql = "INSERT INTO wcf" . WCF_N . "_user_special_trophy
                            (userID, trophyID)
                VALUES      (?, ?)";
        $insertStatement = WCF::getDB()->prepareStatement($sql);

        foreach ($this->getObjects() as $user) {
            WCF::getDB()->beginTransaction();

            // delete all user special trophies for the user
            $deleteStatement->execute([$user->userID]);

            if (!empty($this->parameters['trophyIDs'])) {
                foreach ($this->parameters['trophyIDs'] as $trophyID) {
                    $insertStatement->execute([
                        $user->userID,
                        $trophyID,
                    ]);
                }
            }

            WCF::getDB()->commitTransaction();

            UserStorageHandler::getInstance()->reset([$user->userID], 'specialTrophies');
        }
    }

    /**
     * Sets an avatar for a given user. The given file will be renamed and is gone after this method call.
     *
     * @throws UserInputException If none or more than one user is given.
     * @throws \InvalidArgumentException If the given file is not an image or is incorrectly sized.
     * @since 5.5
     */
    public function setAvatar(): array
    {
        $user = $this->getSingleObject();

        $imageData = \getimagesize($this->parameters['fileLocation']);

        if (!$imageData) {
            throw new \InvalidArgumentException("The given file is not an image.");
        }

        if (
            ($imageData[0] != UserAvatar::AVATAR_SIZE || $imageData[1] != UserAvatar::AVATAR_SIZE)
            && ($imageData[0] != UserAvatar::AVATAR_SIZE_2X || $imageData[1] != UserAvatar::AVATAR_SIZE_2X)
        ) {
            throw new \InvalidArgumentException(
                \sprintf(
                    "The given file does not have the size of %dx%d",
                    UserAvatar::AVATAR_SIZE,
                    UserAvatar::AVATAR_SIZE
                )
            );
        }

        $data = [
            'avatarName' => $this->parameters['filename'] ?? \basename($this->parameters['fileLocation']),
            'avatarExtension' => ImageUtil::getExtensionByMimeType($imageData['mime']),
            'width' => $imageData[0],
            'height' => $imageData[1],
            'userID' => $user->userID,
            'fileHash' => \sha1_file($this->parameters['fileLocation']),
        ];

        // create avatar
        $avatar = UserAvatarEditor::create($data);

        try {
            // check avatar directory
            // and create subdirectory if necessary
            $dir = \dirname($avatar->getLocation(null, false));
            if (!\file_exists($dir)) {
                FileUtil::makePath($dir);
            }

            \rename($this->parameters['fileLocation'], $avatar->getLocation(null, false));

            // Fix the permissions of the file in case the source file was created with restricted
            // permissions (e.g. 0600 instead of 0644). Without this the file might not be readable
            // for the web server if it runs with a different system user.
            FileUtil::makeWritable($avatar->getLocation(null, false));

            // Create the WebP variant or the JPEG fallback of the avatar.
            $avatarEditor = new UserAvatarEditor($avatar);
            if ($avatarEditor->createAvatarVariant()) {
                $avatar = new UserAvatar($avatar->avatarID);
            }

            // update user
            $userEditor = new UserEditor($user->getDecoratedObject());
            $userEditor->update([
                'avatarID' => $avatar->avatarID,
            ]);
        } catch (\Exception $e) {
            $editor = new UserAvatarEditor($avatar);
            $editor->delete();

            throw $e;
        }

        // delete old avatar
        if ($user->avatarID) {
            (new UserAvatarAction([$user->avatarID], 'delete'))->executeAction();
        }

        // reset user storage
        UserStorageHandler::getInstance()->reset([$user->userID], 'avatar');

        return [
            'avatar' => $avatar,
        ];
    }

    /**
     * Validates the 'uploadCoverPhoto' method.
     *
     * @throws  PermissionDeniedException
     * @throws  UserInputException
     * @since   3.1
     */
    public function validateUploadCoverPhoto()
    {
        WCF::getSession()->checkPermissions(['user.profile.coverPhoto.canUploadCoverPhoto']);

        $this->readInteger('userID', true);
        // The `userID` parameter did not exist in 3.1, defaulting to the own user for backwards compatibility.
        if (!$this->parameters['userID']) {
            $this->parameters['userID'] = WCF::getUser()->userID;
        }

        $this->user = new User($this->parameters['userID']);
        if (!$this->user->userID) {
            throw new UserInputException('userID');
        } elseif ($this->user->userID == WCF::getUser()->userID && WCF::getUser()->disableCoverPhoto) {
            throw new PermissionDeniedException();
        } elseif (
            $this->user->userID != WCF::getUser()->userID
            && (!$this->user->canEdit() || !WCF::getSession()->getPermission('admin.user.canDisableCoverPhoto'))
        ) {
            throw new PermissionDeniedException();
        }

        // validate uploaded file
        if (!isset($this->parameters['__files']) || \count($this->parameters['__files']->getFiles()) != 1) {
            throw new UserInputException('files');
        }

        /** @var UploadHandler $uploadHandler */
        $uploadHandler = $this->parameters['__files'];

        $this->uploadFile = $uploadHandler->getFiles()[0];

        $uploadHandler->validateFiles(new UserCoverPhotoUploadFileValidationStrategy());
    }

    /**
     * Uploads a cover photo.
     *
     * @since   3.1
     */
    public function uploadCoverPhoto()
    {
        $saveStrategy = new UserCoverPhotoUploadFileSaveStrategy(
            (!empty($this->parameters['userID']) ? \intval($this->parameters['userID']) : WCF::getUser()->userID)
        );
        /** @noinspection PhpUndefinedMethodInspection */
        $this->parameters['__files']->saveFiles($saveStrategy);

        if ($this->uploadFile->getValidationErrorType()) {
            return [
                'filesize' => $this->uploadFile->getFilesize(),
                'errorMessage' => WCF::getLanguage()->getDynamicVariable(
                    'wcf.user.coverPhoto.upload.error.' . $this->uploadFile->getValidationErrorType(),
                    [
                        'file' => $this->uploadFile,
                    ]
                ),
                'errorType' => $this->uploadFile->getValidationErrorType(),
            ];
        } else {
            return [
                'url' => $saveStrategy->getCoverPhoto()->getURL(),
            ];
        }
    }

    /**
     * Validates the `deleteCoverPhoto` action.
     *
     * @throws  PermissionDeniedException
     * @throws  UserInputException
     */
    public function validateDeleteCoverPhoto()
    {
        $this->readInteger('userID', true);
        // The `userID` parameter did not exist in 3.1, defaulting to the own user for backwards compatibility.
        if (!$this->parameters['userID']) {
            $this->parameters['userID'] = WCF::getUser()->userID;
        }

        $this->user = new User($this->parameters['userID']);
        if (!$this->user->userID) {
            throw new UserInputException('userID');
        } elseif (
            $this->user->userID != WCF::getUser()->userID
            && (!$this->user->canEdit() || !WCF::getSession()->getPermission('admin.user.canDisableCoverPhoto'))
        ) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Deletes the cover photo of the active user.
     *
     * @return  string[]    link to the new cover photo
     */
    public function deleteCoverPhoto()
    {
        if ($this->user->coverPhotoHash) {
            UserProfileRuntimeCache::getInstance()->getObject($this->user->userID)->getCoverPhoto()->delete();

            (new UserEditor($this->user))->update([
                'coverPhotoHash' => null,
                'coverPhotoExtension' => '',
            ]);
        }

        // force-reload the user profile to use a predictable code-path to fetch the cover photo
        UserProfileRuntimeCache::getInstance()->removeObject($this->user->userID);

        return [
            'url' => UserProfileRuntimeCache::getInstance()->getObject($this->user->userID)->getCoverPhoto()->getURL(),
        ];
    }

    /**
     * Returns the user option handler object.
     *
     * @param User $user
     * @param bool $editMode
     * @return  UserOptionHandler
     */
    protected function getOptionHandler(User $user, $editMode = true)
    {
        $optionHandler = new UserOptionHandler(false, '', 'profile');
        if (!$editMode) {
            $optionHandler->showEmptyOptions(false);
            $optionHandler->enableEditMode(false);
        }
        $optionHandler->setUser($user);

        return $optionHandler;
    }
}
