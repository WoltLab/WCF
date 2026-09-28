<?php

namespace wcf\system\importer;

use wcf\command\file\CreateFileFromExistingFile;
use wcf\data\user\group\UserGroup;
use wcf\data\user\rank\UserRank;
use wcf\data\user\rank\UserRankEditor;

/**
 * Imports user ranks.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserRankImporter extends AbstractImporter
{
    /**
     * @inheritDoc
     */
    protected $className = UserRank::class;

    #[\Override]
    public function import(mixed $oldID, array $data, array $additionalData = [])
    {
        $data['groupID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user.group', $data['groupID']);
        if ($data['groupID'] === null) {
            $data['groupID'] = UserGroup::getGroupByType(UserGroup::USERS)->groupID;
        }

        $data['rankImageFileID'] = $this->importImage($data['rankImage'] ?? '');
        unset($data['rankImage']);

        $rank = UserRankEditor::create($data);

        ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.user.rank', $oldID, $rank->rankID);

        return $rank->rankID;
    }

    /**
     * Exporters provide the filename of the image, which the administrator is
     * expected to have copied to `images/rank/`.
     */
    private function importImage(string $rankImage): ?int
    {
        if ($rankImage === '') {
            return null;
        }

        // The filename is taken from the source database and must not be able
        // to point at files outside of the image directory.
        $imageDirectory = \realpath(\WCF_DIR . UserRank::RANK_IMAGE_DIR);
        $pathname = \realpath(\WCF_DIR . UserRank::RANK_IMAGE_DIR . $rankImage);
        if ($imageDirectory === false || $pathname === false) {
            return null;
        }

        if (!\str_starts_with($pathname, $imageDirectory . \DIRECTORY_SEPARATOR)) {
            return null;
        }

        // Ranks of the source can share an image, but each rank owns its file.
        $file = new CreateFileFromExistingFile(
            $pathname,
            \basename($pathname),
            'com.woltlab.wcf.user.rank.image',
            copy: true,
        )();

        return $file?->fileID;
    }
}
