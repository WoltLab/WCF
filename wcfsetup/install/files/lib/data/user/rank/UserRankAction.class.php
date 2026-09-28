<?php

namespace wcf\data\user\rank;

use wcf\command\file\DeleteFiles;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\file\FileList;
use wcf\data\TI18nDatabaseObjectAction;

/**
 * Executes user rank-related actions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<UserRank, UserRankEditor>
 */
class UserRankAction extends AbstractDatabaseObjectAction
{
    use TI18nDatabaseObjectAction;

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.user.rank.canManageRank'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['delete'];

    #[\Override]
    public function create()
    {
        /** @var UserRank $rank */
        $rank = parent::create();

        $this->saveI18nValue($rank);

        return $rank;
    }

    #[\Override]
    public function update()
    {
        $replacedFileIDs = [];
        if (\array_key_exists('rankImageFileID', $this->parameters['data'] ?? [])) {
            if ($this->objects === []) {
                $this->readObjects();
            }

            foreach ($this->objects as $object) {
                $fileID = $object->rankImageFileID;
                if ($fileID !== null && $fileID !== $this->parameters['data']['rankImageFileID']) {
                    $replacedFileIDs[] = $fileID;
                }
            }
        }

        parent::update();

        foreach ($this->objects as $object) {
            $this->saveI18nValue($object->getDecoratedObject());
        }

        $this->deleteFiles($replacedFileIDs);
    }

    #[\Override]
    public function delete()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        $fileIDs = [];
        foreach ($this->objects as $object) {
            if ($object->rankImageFileID !== null) {
                $fileIDs[] = $object->rankImageFileID;
            }
        }

        $count = parent::delete();

        $this->deleteI18nValues();
        $this->deleteFiles($fileIDs);

        return $count;
    }

    /**
     * @param list<int> $fileIDs
     */
    private function deleteFiles(array $fileIDs): void
    {
        if ($fileIDs === []) {
            return;
        }

        $fileList = new FileList();
        $fileList->setObjectIDs($fileIDs);
        $fileList->readObjects();
        $files = \array_values($fileList->getObjects());

        if ($files !== []) {
            new DeleteFiles($files)();
        }
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function getI18nSaveTypes(): array
    {
        return ['rankTitle' => 'wcf.user.rank.userRank\d+'];
    }

    #[\Override]
    public function getLanguageCategory(): string
    {
        return 'wcf.user.rank';
    }

    #[\Override]
    public function getPackageID(): int
    {
        return \PACKAGE_ID;
    }
}
