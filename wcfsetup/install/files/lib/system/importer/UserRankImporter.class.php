<?php

namespace wcf\system\importer;

use wcf\command\file\CreateFileFromExistingFile;
use wcf\data\user\group\UserGroup;
use wcf\data\user\rank\UserRank;
use wcf\data\user\rank\UserRankBuilder;
use wcf\system\l10n\L10nLanguageItemSync;
use wcf\system\l10n\L10nStorage;

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

    private const SHIPPED_LANGUAGE_ITEMS = [
        'wcf.user.rank.administrator',
        'wcf.user.rank.moderator',
        'wcf.user.rank.user0',
        'wcf.user.rank.user1',
        'wcf.user.rank.user2',
        'wcf.user.rank.user3',
        'wcf.user.rank.user4',
        'wcf.user.rank.user5',
    ];

    #[\Override]
    public function import(mixed $oldID, array $data, array $additionalData = [])
    {
        $data['groupID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user.group', $data['groupID']);
        if ($data['groupID'] === null) {
            $data['groupID'] = UserGroup::getGroupByType(UserGroup::USERS)->groupID;
        }

        $rankTitle = (string)$data['rankTitle'];
        $builder = UserRankBuilder::forCreate()
            ->setGroupID($data['groupID'])
            ->setRankImageFileID($this->importImage($data['rankImage'] ?? ''));

        // Exporters of WoltLab Suite pass the title of the default ranks as the
        // name of the language variable shipped with the package.
        $isShippedRank = \in_array($rankTitle, self::SHIPPED_LANGUAGE_ITEMS, true);
        if ($isShippedRank) {
            $builder->setL10nIdentifier($rankTitle);
        } else {
            $builder->setRankTitle([L10nStorage::MONOLINGUAL => $rankTitle]);
        }

        $handledColumns = ['rankID', 'groupID', 'rankTitle', 'rankImage', 'rankImageFileID', 'l10nIdentifier'];
        foreach ($data as $key => $value) {
            if (\in_array($key, $handledColumns, true)) {
                continue;
            }
            if ($value !== null && !\is_string($value) && !\is_int($value) && !\is_float($value)) {
                continue;
            }

            $builder->setCustomProperty($key, $value);
        }

        $rank = $builder->create();

        if ($isShippedRank) {
            L10nLanguageItemSync::sync(UserRank::getL10nDefinition());
        }

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
