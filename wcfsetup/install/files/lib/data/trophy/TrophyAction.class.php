<?php

namespace wcf\data\trophy;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISortableAction;
use wcf\data\IToggleAction;
use wcf\data\IUploadAction;
use wcf\data\TDatabaseObjectToggle;
use wcf\data\user\trophy\UserTrophyAction;
use wcf\data\user\trophy\UserTrophyList;
use wcf\data\user\UserAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\image\ImageHandler;
use wcf\system\upload\TrophyImageUploadFileValidationStrategy;
use wcf\system\upload\UploadFile;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Trophy related actions.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 *
 * @method  TrophyEditor[]      getObjects()
 * @method  TrophyEditor        getSingleObject()
 */
class TrophyAction extends AbstractDatabaseObjectAction implements IToggleAction, IUploadAction, ISortableAction
{
    use TDatabaseObjectToggle;

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.trophy.canManageTrophy'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.trophy.canManageTrophy'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['toggle', 'delete'];

    /**
     * @inheritDoc
     * @return  Trophy
     */
    public function create()
    {
        $showOrder = 0;
        if (isset($this->parameters['data']['showOrder'])) {
            $showOrder = $this->parameters['data']['showOrder'];
            unset($this->parameters['data']['showOrder']);
        }

        /** @var Trophy $trophy */
        $trophy = parent::create();

        if (isset($this->parameters['tmpHash']) && $this->parameters['data']['type'] === Trophy::TYPE_IMAGE) {
            $this->updateTrophyImage($trophy);
        }

        $trophyEditor = new TrophyEditor($trophy);
        $trophyEditor->setShowOrder($showOrder);

        return new Trophy($trophy->trophyID);
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        // update trophy points
        $userTrophyList = new UserTrophyList();
        if (!empty($userTrophyList->sqlJoins)) {
            $userTrophyList->sqlJoins .= ' ';
        }
        $userTrophyList->sqlJoins .= '
            LEFT JOIN   wcf1_trophy trophy
            ON          user_trophy.trophyID = trophy.trophyID
            LEFT JOIN   wcf1_category category
            ON          trophy.categoryID = category.categoryID';

        $userTrophyList->getConditionBuilder()->add('trophy.isDisabled = ?', [0]);
        $userTrophyList->getConditionBuilder()->add('category.isDisabled = ?', [0]);
        $userTrophyList->getConditionBuilder()->add('user_trophy.trophyID IN (?)', [$this->getObjectIDs()]);
        $userTrophyList->readObjects();

        $userTrophyAction = new UserTrophyAction($userTrophyList->getObjects(), 'delete');
        $userTrophyAction->executeAction();

        foreach ($this->getObjects() as $trophy) {
            if ($trophy->iconFile) {
                @\unlink(WCF_DIR . 'images/trophy/' . $trophy->iconFile);
            }
        }

        $returnValues = parent::delete();

        UserStorageHandler::getInstance()->resetAll('specialTrophies');

        return $returnValues;
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
        parent::update();

        if (isset($this->parameters['data']['type']) && $this->parameters['data']['type'] === Trophy::TYPE_IMAGE) {
            foreach ($this->getObjects() as $trophy) {
                if (isset($this->parameters['tmpHash'])) {
                    $this->updateTrophyImage($trophy->getDecoratedObject());
                }
            }
        }

        if (\count($this->objects) == 1 && isset($this->parameters['data']['showOrder']) && $this->parameters['data']['showOrder'] != \reset($this->objects)->showOrder) {
            \reset($this->objects)->setShowOrder($this->parameters['data']['showOrder']);
        }
    }

    /**
     * @inheritDoc
     */
    public function toggle()
    {
        $enabledTrophyIDs = [];
        $disabledTrophyIDs = [];

        foreach ($this->getObjects() as $trophy) {
            $trophy->update(['isDisabled' => $trophy->isDisabled ? 0 : 1]);

            if (!$trophy->isDisabled) {
                $disabledTrophyIDs[] = $trophy->trophyID;
            } else {
                $enabledTrophyIDs[] = $trophy->trophyID;
            }
        }

        if (!empty($disabledTrophyIDs)) {
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('trophyID IN (?)', [$disabledTrophyIDs]);
            $sql = "DELETE FROM wcf1_user_special_trophy
                    " . $conditionBuilder;
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditionBuilder->getParameters());

            // update trophy points
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('trophyID IN (?)', [$disabledTrophyIDs]);
            $sql = "SELECT      COUNT(*) as count, userID
                    FROM        wcf1_user_trophy
                    " . $conditionBuilder . "
                    GROUP BY    userID";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditionBuilder->getParameters());

            while ($row = $statement->fetchArray()) {
                $userAction = new UserAction([$row['userID']], 'update', [
                    'counters' => [
                        'trophyPoints' => $row['count'] * -1,
                    ],
                ]);
                $userAction->executeAction();
            }
        }

        if (!empty($enabledTrophyIDs)) {
            // update trophy points
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('trophyID IN (?)', [$enabledTrophyIDs]);
            $sql = "SELECT      COUNT(*) as count, userID
                    FROM        wcf1_user_trophy
                    " . $conditionBuilder . "
                    GROUP BY    userID";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditionBuilder->getParameters());

            while ($row = $statement->fetchArray()) {
                $userAction = new UserAction([$row['userID']], 'update', [
                    'counters' => [
                        'trophyPoints' => $row['count'],
                    ],
                ]);
                $userAction->executeAction();
            }
        }

        UserStorageHandler::getInstance()->resetAll('specialTrophies');
    }

    /**
     * @inheritDoc
     */
    public function validateUpload()
    {
        WCF::getSession()->checkPermissions(['admin.trophy.canManageTrophy']);

        $this->readString('tmpHash');
        $this->readInteger('trophyID', true);

        if ($this->parameters['trophyID']) {
            $this->parameters['trophy'] = new Trophy($this->parameters['trophyID']);

            if (!$this->parameters['trophy']->trophyID) {
                throw new IllegalLinkException();
            }
        }

        $this->parameters['__files']->validateFiles(new TrophyImageUploadFileValidationStrategy());

        /** @var UploadFile[] $files */
        $files = $this->parameters['__files']->getFiles();

        // only one file is allowed
        if (\count($files) !== 1) {
            throw new UserInputException('file');
        }

        $this->parameters['file'] = \reset($files);

        if ($this->parameters['file']->getValidationErrorType()) {
            throw new UserInputException('file', $this->parameters['file']->getValidationErrorType());
        }
    }

    /**
     * @inheritDoc
     */
    public function upload()
    {
        $fileName = WCF_DIR . 'images/trophy/tmp_' . $this->parameters['tmpHash'] . '.' . $this->parameters['file']->getFileExtension();
        if ($this->parameters['file']->getImageData()['height'] > 128) {
            $adapter = ImageHandler::getInstance()->getAdapter();
            $adapter->loadFile($this->parameters['file']->getLocation());
            $adapter->resize(
                0,
                0,
                $this->parameters['file']->getImageData()['height'],
                $this->parameters['file']->getImageData()['height'],
                128,
                128
            );
            $adapter->writeImage($adapter->getImage(), $fileName);
        } else {
            \copy($this->parameters['file']->getLocation(), $fileName);
        }

        // remove old image
        @\unlink($this->parameters['file']->getLocation());

        // store extension within session variables
        WCF::getSession()->register(
            'trophyImage-' . $this->parameters['tmpHash'],
            $this->parameters['file']->getFileExtension()
        );

        if ($this->parameters['trophyID']) {
            $this->updateTrophyImage($this->parameters['trophy']);

            return [
                'url' => WCF::getPath() . 'images/trophy/trophyImage-' . $this->parameters['trophyID'] . '.' . $this->parameters['file']->getFileExtension(),
            ];
        }

        return [
            'url' => WCF::getPath() . 'images/trophy/' . \basename($fileName),
        ];
    }

    /**
     * Updates style preview image.
     *
     * @param Trophy $trophy
     */
    protected function updateTrophyImage(Trophy $trophy)
    {
        if (!isset($this->parameters['tmpHash'])) {
            return;
        }

        $fileExtension = WCF::getSession()->getVar('trophyImage-' . $this->parameters['tmpHash']);
        if ($fileExtension !== null) {
            $oldFilename = WCF_DIR . 'images/trophy/tmp_' . $this->parameters['tmpHash'] . '.' . $fileExtension;
            if (\file_exists($oldFilename)) {
                $filename = 'trophyImage-' . $trophy->trophyID . '.' . $fileExtension;
                if (@\rename($oldFilename, WCF_DIR . 'images/trophy/' . $filename)) {
                    // delete old file if it has a different file extension
                    if ($trophy->iconFile != $filename) {
                        @\unlink(WCF_DIR . 'images/trophy/' . $trophy->iconFile);

                        $trophyEditor = new TrophyEditor($trophy);
                        $trophyEditor->update([
                            'iconFile' => $filename,
                        ]);
                    }
                } else {
                    // remove temp file
                    @\unlink($oldFilename);
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function validateUpdatePosition()
    {
        WCF::getSession()->checkPermissions($this->permissionsUpdate);

        if (!isset($this->parameters['data']['structure']) || !\is_array($this->parameters['data']['structure'])) {
            throw new UserInputException('structure');
        }

        $trophyList = new TrophyList();
        $trophyList->setObjectIDs($this->parameters['data']['structure'][0]);
        $trophyList->readObjects();
        if (\count($trophyList) !== \count($this->parameters['data']['structure'][0])) {
            throw new UserInputException('structure');
        }

        $this->readInteger('offset', true, 'data');
    }

    /**
     * @inheritDoc
     */
    public function updatePosition()
    {
        $sql = "UPDATE  wcf1_trophy
                SET     showOrder = ?
                WHERE   trophyID = ?";
        $statement = WCF::getDB()->prepare($sql);

        $showOrder = $this->parameters['data']['offset'];
        WCF::getDB()->beginTransaction();
        foreach ($this->parameters['data']['structure'][0] as $trophyID) {
            $statement->execute([
                $showOrder++,
                $trophyID,
            ]);
        }
        WCF::getDB()->commitTransaction();
    }
}
