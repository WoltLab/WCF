<?php

namespace wcf\data\trophy;

use wcf\command\trophy\DisableTrophy;
use wcf\command\trophy\EnableTrophy;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\data\IUploadAction;
use wcf\data\user\trophy\UserTrophyAction;
use wcf\data\user\trophy\UserTrophyList;
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
 * @extends AbstractDatabaseObjectAction<Trophy, TrophyEditor>
 */
class TrophyAction extends AbstractDatabaseObjectAction implements IToggleAction, IUploadAction
{
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
     */
    public function create()
    {
        $showOrder = 0;
        if (isset($this->parameters['data']['showOrder'])) {
            $showOrder = $this->parameters['data']['showOrder'];
            unset($this->parameters['data']['showOrder']);
        }

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
     * @deprecated 6.3
     */
    public function validateToggle()
    {
        $this->validateUpdate();
    }

    /**
     * @inheritDoc
     * @deprecated 6.3 use the `EnableTrophy` or `DisableTrophy` commands instead.
     */
    public function toggle()
    {
        foreach ($this->getObjects() as $editor) {
            if ($editor->isDisabled) {
                (new EnableTrophy($editor->getDecoratedObject()))();
            } else {
                (new DisableTrophy($editor->getDecoratedObject()))();
            }
        }
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
     * @return void
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
}
