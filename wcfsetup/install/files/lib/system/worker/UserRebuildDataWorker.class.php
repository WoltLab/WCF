<?php

namespace wcf\system\worker;

use wcf\data\reaction\type\ReactionTypeCache;
use wcf\data\user\avatar\UserAvatar;
use wcf\data\user\avatar\UserAvatarEditor;
use wcf\data\user\avatar\UserAvatarList;
use wcf\data\user\cover\photo\DefaultUserCoverPhoto;
use wcf\data\user\cover\photo\IWebpUserCoverPhoto;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\data\user\UserList;
use wcf\data\user\UserProfileAction;
use wcf\data\user\UserProfileList;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\image\ImageHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Worker implementation for updating users.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserList    getObjectList()
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

            // retrieve permissions
            $userIDs = [];
            foreach ($users as $user) {
                $userIDs[] = $user->userID;
            }
            $userPermissions = $this->getBulkUserPermissions(
                $userIDs,
                ['user.message.disallowedBBCodes', 'user.signature.disallowedBBCodes']
            );

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
            }
            WCF::getDB()->commitTransaction();

            // update old/imported avatars
            $avatarList = new UserAvatarList();
            $avatarList->getConditionBuilder()->add('user_avatar.userID IN (?)', [$userIDs]);
            $avatarList->getConditionBuilder()->add(
                '(
                    (user_avatar.width <> ? OR user_avatar.height <> ?)
                    OR (user_avatar.hasWebP = ? AND user_avatar.avatarExtension <> ?)
                )',
                [
                    UserAvatar::AVATAR_SIZE,
                    UserAvatar::AVATAR_SIZE,
                    0,
                    "gif",
                ]
            );
            $avatarList->readObjects();
            $resetAvatarCache = [];
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
                    $width = $height = \min($width, $height, UserAvatar::AVATAR_SIZE);
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

                if ($width != UserAvatar::AVATAR_SIZE || $height != UserAvatar::AVATAR_SIZE) {
                    // resize avatar
                    $adapter = ImageHandler::getInstance()->getAdapter();

                    try {
                        $adapter->loadFile($avatar->getLocation());
                    } catch (SystemException $e) {
                        // broken image
                        $editor->delete();
                        continue;
                    }

                    $adapter->resize(0, 0, $width, $height, UserAvatar::AVATAR_SIZE, UserAvatar::AVATAR_SIZE);
                    $adapter->writeImage($adapter->getImage(), $avatar->getLocation());
                    $width = $height = UserAvatar::AVATAR_SIZE;
                }

                $editor->createAvatarVariant();

                $editor->update([
                    'width' => $width,
                    'height' => $height,
                ]);
            }

            // Reset the avatar cache for all avatars that had been processed.
            if (!empty($resetAvatarCache)) {
                UserStorageHandler::getInstance()->reset($resetAvatarCache, 'avatar');
            }

            // Create WebP variants of existing cover photos.
            $userProfiles = new UserProfileList();
            $userProfiles->getConditionBuilder()->add("user_table.userID IN (?)", [$userIDs]);
            $userProfiles->getConditionBuilder()->add("user_table.coverPhotoHash IS NOT NULL");
            $userProfiles->getConditionBuilder()->add("user_table.coverPhotoHasWebP = ?", [0]);
            $userProfiles->readObjects();
            foreach ($userProfiles as $userProfile) {
                $editor = new UserEditor($userProfile->getDecoratedObject());
                $coverPhoto = $userProfile->getCoverPhoto(true);
                if ($coverPhoto instanceof DefaultUserCoverPhoto) {
                    // The default cover photo can be returned if the user has a
                    // cover photo, but it has been disabled by an administrator.
                    continue;
                }

                // If neither the regular, nor the WebP variant is readable then the
                // cover photo is missing and we must clear the database information.
                if (
                    !\is_readable($coverPhoto->getLocation(false))
                    && !\is_readable($coverPhoto->getLocation(true))
                ) {
                    $editor->update([
                        'coverPhotoHash' => null,
                        'coverPhotoExtension' => '',
                    ]);

                    continue;
                }

                if ($coverPhoto instanceof IWebpUserCoverPhoto) {
                    $result = $coverPhoto->createWebpVariant();
                    if ($result !== null) {
                        $data['coverPhotoHasWebP'] = 1;

                        // A fallback jpeg image was just created.
                        if ($result === false) {
                            $data['coverPhotoExtension'] = 'jpg';
                        }

                        $editor->update($data);
                    }
                }
            }
        }
    }
}
