<?php

namespace wcf\system\worker;

use wcf\data\file\FileEditor;
use wcf\data\file\FileList;
use wcf\data\reaction\type\ReactionTypeCache;
use wcf\data\user\avatar\UserAvatarEditor;
use wcf\data\user\avatar\UserAvatarList;
use wcf\data\user\cover\photo\UserCoverPhoto;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\data\user\UserList;
use wcf\data\user\UserProfile;
use wcf\data\user\UserProfileAction;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\file\processor\UserAvatarFileProcessor;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\image\ImageHandler;
use wcf\system\user\command\SetCoverPhoto;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Worker implementation for updating users.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractLinearRebuildDataWorker<UserList>
 */
final class UserRebuildDataWorker extends AbstractLinearRebuildDataWorker
{
    /**
     * @inheritDoc
     */
    protected $objectListClassName = UserList::class;

    /**
     * @inheritDoc
     */
    protected $limit = 50;

    #[\Override]
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->sqlSelects = 'user_option_value.userOption' . User::getUserOptionID('aboutMe') . ' AS aboutMe';
        $this->objectList->sqlSelects .= ',user_option_value.userOption' . User::getUserOptionID('canViewOnlineStatus') . ' AS canViewOnlineStatus';
        $this->objectList->sqlJoins = "
            LEFT JOIN   wcf1_user_option_value user_option_value
            ON          user_option_value.userID = user_table.userID";
    }

    #[\Override]
    public function execute()
    {
        parent::execute();

        if (\count($this->getObjectList()) === 0) {
            return;
        }

        $users = $userIDs = [];
        foreach ($this->getObjectList() as $user) {
            $users[] = new UserEditor($user);
            $userIDs[] = $user->userID;
        }

        // update user ranks
        if (!empty($users)) {
            $action = new UserProfileAction($users, 'updateUserOnlineMarking');
            $action->executeAction();
        }

        $this->updateUserOnlineStatus($users);

        if (!empty($userIDs)) {
            // update article counter
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('user_table.userID IN (?)', [$userIDs]);
            $sql = "UPDATE  wcf1_user user_table
                    SET     articles = (
                                SELECT  COUNT(*)
                                FROM    wcf1_article
                                WHERE   userID = user_table.userID
                            )
                    " . $conditionBuilder;
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditionBuilder->getParameters());

            // update like counter
            if (MODULE_LIKE) {
                $sql = "UPDATE  wcf1_user user_table
                        SET";

                $reactionTypeIDs = \array_keys(ReactionTypeCache::getInstance()->getReactionTypes());
                if (!empty($reactionTypeIDs)) {
                    $sql .= "
                        likesReceived = (
                            SELECT  COUNT(*)
                            FROM    wcf1_like
                            WHERE   objectUserID = user_table.userID
                                AND reactionTypeID IN (" . \implode(',', $reactionTypeIDs) . ")
                        )";
                } else {
                    $sql .= " likesReceived = 0";
                }

                $sql .= " " . $conditionBuilder;
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute($conditionBuilder->getParameters());
            }

            // update trophy points
            if (MODULE_TROPHY) {
                $sql = "UPDATE  wcf1_user user_table
                        SET     trophyPoints = (
                                    SELECT      COUNT(*)
                                    FROM        wcf1_user_trophy user_trophy
                                    LEFT JOIN   wcf1_trophy trophy
                                    ON          user_trophy.trophyID = trophy.trophyID
                                    LEFT JOIN   wcf1_category trophy_category
                                    ON          trophy.categoryID = trophy_category.categoryID
                                    WHERE           user_trophy.userID = user_table.userID
                                                AND trophy.isDisabled = 0
                                                AND trophy_category.isDisabled = 0
                                )
                        " . $conditionBuilder;
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute($conditionBuilder->getParameters());
            }

            // update signatures and about me
            $sql = "UPDATE  wcf1_user_option_value
                    SET     userOption" . User::getUserOptionID('aboutMe') . " = ?
                    WHERE   userID = ?";
            $statement = WCF::getDB()->prepare($sql);

            // retrieve permissions and cache avatar files
            $userIDs = [];
            $avatarFileIDs = [];
            foreach ($users as $user) {
                $userIDs[] = $user->userID;

                if ($user->avatarFileID !== null) {
                    $avatarFileIDs[] = $user->avatarFileID;
                }
            }
            $userPermissions = $this->getBulkUserPermissions(
                $userIDs,
                ['user.message.disallowedBBCodes', 'user.signature.disallowedBBCodes']
            );

            $avatarFiles = [];
            if ($avatarFileIDs !== []) {
                $fileList = new FileList();
                $fileList->setObjectIDs($avatarFileIDs);
                $fileList->readObjects();
                $avatarFiles = $fileList->getObjects();
            }

            $htmlInputProcessor = new HtmlInputProcessor();
            WCF::getDB()->beginTransaction();
            /** @var UserEditor $user */
            foreach ($users as $user) {
                BBCodeHandler::getInstance()->setDisallowedBBCodes(\explode(
                    ',',
                    $this->getBulkUserPermissionValue(
                        $userPermissions,
                        $user->userID,
                        'user.signature.disallowedBBCodes'
                    )
                ));

                if ($user->signature) {
                    if (!$user->signatureEnableHtml) {
                        $htmlInputProcessor->process(
                            $user->signature,
                            'com.woltlab.wcf.user.signature',
                            $user->userID,
                            true
                        );

                        $user->update([
                            'signature' => $htmlInputProcessor->getHtml(),
                            'signatureEnableHtml' => 1,
                        ]);
                    } else {
                        $htmlInputProcessor->reprocess($user->signature, 'com.woltlab.wcf.user.signature', $user->userID);
                        $user->update(['signature' => $htmlInputProcessor->getHtml()]);
                    }
                }

                if ($user->aboutMe) {
                    BBCodeHandler::getInstance()->setDisallowedBBCodes(\explode(
                        ',',
                        $this->getBulkUserPermissionValue(
                            $userPermissions,
                            $user->userID,
                            'user.message.disallowedBBCodes'
                        )
                    ));

                    if (!$user->signatureEnableHtml) {
                        $htmlInputProcessor->process(
                            $user->aboutMe,
                            'com.woltlab.wcf.user.aboutMe',
                            $user->userID,
                            true
                        );
                    } else {
                        $htmlInputProcessor->reprocess($user->aboutMe, 'com.woltlab.wcf.user.aboutMe', $user->userID);
                    }

                    $html = $htmlInputProcessor->getHtml();
                    if (\mb_strlen($html) > 65535) {
                        // content does not fit the available space, and any
                        // attempts to truncate it will yield awkward results
                        $html = '';
                    }

                    $statement->execute([$html, $user->userID]);
                }

                if ($user->avatarFileID !== null && $user->avatarPathname === null) {
                    $file = $avatarFiles[$user->avatarFileID] ?? null;
                    \assert(
                        $file !== null,
                        "Expected the avatarFileID {$user->avatarFileID} of user {$user->userID} to exist."
                    );

                    $filename = $file->getSourceFilenameWebp() ?? $file->getSourceFilename();
                    $pathname = $file->getRelativePath() . $filename;

                    $user->update([
                        'avatarPathname' => $pathname,
                    ]);
                }
            }
            WCF::getDB()->commitTransaction();

            // update old/imported avatars
            $avatarList = new UserAvatarList();
            $avatarList->getConditionBuilder()->add('user_avatar.userID IN (?)', [$userIDs]);
            $avatarList->readObjects();
            $resetAvatarCache = [];

            $sql = "UPDATE wcf1_user
                    SET    avatarFileID = ?
                    WHERE  userID = ?";
            $avatarUpdateStatement = WCF::getDB()->prepare($sql);

            foreach ($avatarList as $avatar) {
                $resetAvatarCache[] = $avatar->userID;

                $editor = new UserAvatarEditor($avatar);
                if (!\file_exists($avatar->getLocation()) || @\getimagesize($avatar->getLocation()) === false) {
                    // delete avatars that are missing or broken
                    $editor->delete();
                    continue;
                }

                $width = $avatar->width;
                $height = $avatar->height;
                if ($width != $height) {
                    // make avatar quadratic
                    // minimum size is 128x128, maximum size is 256x256
                    $width = $height = \min(
                        \max($avatar->width, $avatar->height, UserAvatarFileProcessor::AVATAR_SIZE),
                        UserAvatarFileProcessor::AVATAR_SIZE_2X
                    );
                    $adapter = ImageHandler::getInstance()->getAdapter();

                    try {
                        $adapter->loadFile($avatar->getLocation());
                    } catch (SystemException $e) {
                        // broken image
                        $editor->delete();
                        continue;
                    }

                    $thumbnail = $adapter->createThumbnail($width, $height, false);
                    $adapter->writeImage($thumbnail, $avatar->getLocation());
                    // Clear thumbnail as soon as possible to free up the memory.
                    $thumbnail = null;
                }

                if (
                    $width != UserAvatarFileProcessor::AVATAR_SIZE
                    && $width != UserAvatarFileProcessor::AVATAR_SIZE_2X
                ) {
                    // resize avatar
                    $adapter = ImageHandler::getInstance()->getAdapter();

                    try {
                        $adapter->loadFile($avatar->getLocation());
                    } catch (SystemException $e) {
                        // broken image
                        $editor->delete();
                        continue;
                    }

                    if ($width > UserAvatarFileProcessor::AVATAR_SIZE_2X) {
                        $adapter->resize(
                            0,
                            0,
                            $width,
                            $height,
                            UserAvatarFileProcessor::AVATAR_SIZE_2X,
                            UserAvatarFileProcessor::AVATAR_SIZE_2X
                        );
                    } else {
                        $adapter->resize(
                            0,
                            0,
                            $width,
                            $height,
                            UserAvatarFileProcessor::AVATAR_SIZE,
                            UserAvatarFileProcessor::AVATAR_SIZE
                        );
                    }
                    $adapter->writeImage($adapter->getImage(), $avatar->getLocation());
                }

                $file = FileEditor::createFromExistingFile(
                    $avatar->getLocation(),
                    $avatar->avatarName,
                    'com.woltlab.wcf.user.avatar'
                );
                $editor->delete();

                if ($file === null) {
                    continue;
                }

                $avatarUpdateStatement->execute([
                    $file->fileID,
                    $avatar->userID
                ]);
            }

            // Reset the avatar cache for all avatars that had been processed.
            if (!empty($resetAvatarCache)) {
                UserStorageHandler::getInstance()->reset($resetAvatarCache, 'avatar');
            }

            // Migrate old cover photos into the new file system.
            $userProfiles = new UserList();
            $userProfiles->getConditionBuilder()->add("user_table.userID IN (?)", [$userIDs]);
            $userProfiles->getConditionBuilder()->add("user_table.coverPhotoHash IS NOT NULL");
            $userProfiles->readObjects();
            foreach ($userProfiles as $user) {
                $file = FileEditor::createFromExistingFile(
                    UserCoverPhoto::getLegacyLocation($user, false),
                    $user->coverPhotoHash . '.' . $user->coverPhotoExtension,
                    'com.woltlab.wcf.user.coverPhoto',
                );

                (new SetCoverPhoto($user, $file))();

                // Delete the old cover photo files.
                $oldCoverPhotoLocation = UserCoverPhoto::getLegacyLocation($user, false);
                $oldCoverPhotoWebPLocation = UserCoverPhoto::getLegacyLocation($user, true);

                if ($oldCoverPhotoLocation && \file_exists($oldCoverPhotoLocation)) {
                    @\unlink($oldCoverPhotoLocation);
                }
                if ($oldCoverPhotoWebPLocation && \file_exists($oldCoverPhotoWebPLocation)) {
                    @\unlink($oldCoverPhotoWebPLocation);
                }
            }
        }
    }

    /**
     * This method checks whether a user has restricted the visibility of their online status in the past,
     * but has since lost the permission for it.
     * In this case, the visibility of the online status is automatically set to default.
     *
     * @param UserEditor[] $users
     */
    private function updateUserOnlineStatus(array $users): void
    {
        foreach ($users as $user) {
            if ($user->canViewOnlineStatus == UserProfile::ACCESS_EVERYONE) {
                continue;
            }
            $userProfile = new UserProfile($user->getDecoratedObject());
            if ($userProfile->getPermission('user.profile.canHideOnlineStatus')) {
                continue;
            }

            $user->updateUserOptions([
                User::getUserOptionID('canViewOnlineStatus') => UserProfile::ACCESS_EVERYONE,
            ]);
        }
    }
}
