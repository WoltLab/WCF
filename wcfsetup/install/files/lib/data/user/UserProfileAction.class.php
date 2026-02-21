<?php

namespace wcf\data\user;

use wcf\data\object\type\ObjectTypeCache;
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
use wcf\system\user\group\assignment\UserGroupAssignmentHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\MessageUtil;
use wcf\util\StringUtil;

/**
 * Executes user profile-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserProfileAction extends UserAction
{
    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['getDetailedActivityPointList'];

    /**
     * @var User
     */
    public $user;

    /**
     * user profile object
     * @var ?UserProfile
     */
    public $userProfile;

    /**
     * uploaded file
     * @var ?UploadFile
     */
    public $uploadFile;

    /**
     * Validates parameters for signature preview.
     *
     * @return void
     */
    public function validateGetMessagePreview()
    {
        $this->readString('message', true, 'data');
    }

    /**
     * Returns a rendered signature preview.
     *
     * @return array{message: string, raw: string}
     * @throws UserInputException
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
     * Validates detailed activity point list
     *
     * @return void
     * @throws UserInputException
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
     * @return array{template: string, userID: int}
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
                FROM    wcf1_user_activity_point
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
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
            'template' => WCF::getTPL()->render(
                'wcf',
                'detailedActivityPointList',
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
     * @return void
     * @throws PermissionDeniedException
     * @throws UserInputException
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
     * @return array{template: string}
     */
    public function beginEdit()
    {
        $optionTree = $this->getOptionHandler($this->userProfile->getDecoratedObject())->getOptionTree();

        return [
            'template' => WCF::getTPL()->render('wcf', 'userProfileAboutEditable', [
                'errorType' => [],
                'optionTree' => $optionTree,
                '__userTitle' => $this->userProfile->userTitle,
            ]),
        ];
    }

    /**
     * Validates parameters to save changes to user profile.
     *
     * @return void
     * @throws PermissionDeniedException
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
     * @return array{success: bool, template: string}
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

            return [
                'success' => true,
                'template' => WCF::getTPL()->render('wcf', 'userProfileAbout', [
                    'options' => $options,
                    'userID' => $this->userProfile->userID,
                ]),
            ];
        } else {
            // validation failed
            return [
                'success' => false,
                'template' => WCF::getTPL()->render('wcf', 'userProfileAboutEditable', [
                    'errorType' => $errors,
                    'optionTree' => $optionHandler->getOptionTree(),
                    '__userTitle' => $userTitle !== null ? $userTitle : $this->userProfile->userTitle,
                ]),
            ];
        }
    }

    /**
     * Updates user ranks.
     *
     * @return void
     */
    public function updateUserRank()
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        $userToRank = [];
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
                    FROM        wcf1_user_rank user_rank
                    LEFT JOIN   wcf1_user_group user_group
                    ON          user_group.groupID = user_rank.groupID
                    " . $conditionBuilder . "
                    ORDER BY    user_group.priority DESC, user_rank.requiredPoints DESC, user_rank.requiredGender DESC";
            $statement = WCF::getDB()->prepare($sql, 1);
            $statement->execute($conditionBuilder->getParameters());
            $row = $statement->fetchArray();
            if ($row === false) {
                if ($user->rankID) {
                    $userToRank[$user->userID] = null;
                }
            } else {
                if ($row['rankID'] != $user->rankID) {
                    $userToRank[$user->userID] = $row['rankID'];
                }
            }
        }

        if (!empty($userToRank)) {
            $sql = "UPDATE  wcf1_user
                    SET     rankID = ?
                    WHERE   userID = ?";
            $statement = WCF::getDB()->prepare($sql);

            WCF::getDB()->beginTransaction();
            foreach ($userToRank as $userID => $rankID) {
                $statement->execute([
                    $rankID,
                    $userID,
                ]);
            }
            WCF::getDB()->commitTransaction();
        }
    }

    /**
     * Updates user online markings.
     *
     * @return void
     */
    public function updateUserOnlineMarking()
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        $fixUserGroupIDs = $userToGroup = $removeFromGroupIDs = [];
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

                if (\in_array(UserGroup::USERS, $groupIDs)) {
                    if (!isset($removeFromGroupIDs[$user->userID])) {
                        $removeFromGroupIDs[$user->userID] = [];
                    }

                    $removeFromGroupIDs[$user->userID][] = UserGroup::USERS;
                }
            } else {
                if (!\in_array(UserGroup::USERS, $groupIDs)) {
                    if (!isset($fixUserGroupIDs[$user->userID])) {
                        $fixUserGroupIDs[$user->userID] = [];
                    }
                    $fixUserGroupIDs[$user->userID][] = UserGroup::USERS;
                    $groupIDs[] = UserGroup::USERS;
                }

                if (\in_array(UserGroup::GUESTS, $groupIDs)) {
                    if (!isset($removeFromGroupIDs[$user->userID])) {
                        $removeFromGroupIDs[$user->userID] = [];
                    }

                    $removeFromGroupIDs[$user->userID][] = UserGroup::GUESTS;
                }
            }
            $newGroupIDs[$user->userID] = $groupIDs;

            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('groupID IN (?)', [$groupIDs]);

            $sql = "SELECT      groupID
                    FROM        wcf1_user_group
                    " . $conditionBuilder . "
                    ORDER BY    priority DESC";
            $statement = WCF::getDB()->prepare($sql, 1);
            $statement->execute($conditionBuilder->getParameters());
            $row = $statement->fetchArray();
            if ($row['groupID'] != $user->userOnlineGroupID) {
                $userToGroup[$user->userID] = $row['groupID'];
            }
        }

        // add users to missing default user groups
        if (!empty($fixUserGroupIDs)) {
            $sql = "INSERT INTO wcf1_user_to_group
                                (userID, groupID)
                    VALUES      (?, ?)";
            $statement = WCF::getDB()->prepare($sql);

            WCF::getDB()->beginTransaction();
            foreach ($fixUserGroupIDs as $userID => $groupIDs) {
                foreach ($groupIDs as $groupID) {
                    $statement->execute([$userID, $groupID]);
                }

                UserStorageHandler::getInstance()->update($userID, 'groupIDs', \serialize($newGroupIDs[$userID]));
            }
            WCF::getDB()->commitTransaction();
        }

        if ($removeFromGroupIDs !== []) {
            $sql = "DELETE FROM wcf1_user_to_group
                    WHERE       userID = ?
                            AND groupID = ?";
            $statement = WCF::getDB()->prepare($sql);

            WCF::getDB()->beginTransaction();
            foreach ($removeFromGroupIDs as $userID => $groupIDs) {
                foreach ($groupIDs as $groupID) {
                    $statement->execute([$userID, $groupID]);
                }
            }
            WCF::getDB()->commitTransaction();
        }

        if (!empty($userToGroup)) {
            $sql = "UPDATE  wcf1_user
                    SET     userOnlineGroupID = ?
                    WHERE   userID = ?";
            $statement = WCF::getDB()->prepare($sql);

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
     *
     * @return void
     */
    public function updateSpecialTrophies()
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        $sql = "DELETE FROM wcf1_user_special_trophy
                WHERE userID = ?";
        $deleteStatement = WCF::getDB()->prepare($sql);

        $sql = "INSERT INTO wcf1_user_special_trophy
                            (userID, trophyID)
                VALUES      (?, ?)";
        $insertStatement = WCF::getDB()->prepare($sql);

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
     * Returns the user option handler object.
     *
     * @param bool $editMode
     * @return UserOptionHandler
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
