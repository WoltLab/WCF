<?php

namespace wcf\data\user;

use wcf\command\user\RefreshUserOnlineMarkings;
use wcf\command\user\RefreshUserRanks;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\upload\UploadFile;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

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
     * @var ?User
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
        if ($disallowedBBCodes !== []) {
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
        if (\count($this->objectIDs) !== 1) {
            throw new UserInputException('objectIDs');
        }
        $this->userProfile = UserProfileRuntimeCache::getInstance()->getObject(\reset($this->objectIDs));

        if ($this->userProfile === null) {
            throw new UserInputException('objectIDs');
        }

        if (
            $this->userProfile->userID !== WCF::getUser()->userID
            && !WCF::getSession()->hasPermission('user.profile.canViewUserProfile')
        ) {
            throw new PermissionDeniedException();
        }

        if ($this->userProfile->isProtected()) {
            throw new PermissionDeniedException();
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
     * Updates user ranks.
     *
     * @return void
     * @deprecated 6.3 use the `RefreshUserRanks` command instead.
     */
    public function updateUserRank()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        $users = \array_map(
            static fn (UserEditor $editor) => $editor->getDecoratedObject(),
            $this->getObjects()
        );
        (new RefreshUserRanks(\array_values($users)))();
    }

    /**
     * Updates user online markings.
     *
     * @return void
     * @deprecated 6.3 use the `RefreshUserOnlineMarkings` command instead.
     */
    public function updateUserOnlineMarking()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        $users = \array_map(
            static fn (UserEditor $editor) => $editor->getDecoratedObject(),
            $this->getObjects()
        );
        (new RefreshUserOnlineMarkings(\array_values($users)))();
    }

    /**
     * Updates the special trophies.
     *
     * @return void
     */
    public function updateSpecialTrophies()
    {
        if ($this->objects === []) {
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
}
