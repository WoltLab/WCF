<?php

namespace wcf\system\importer;

use wcf\command\file\CreateFileFromExistingFile;
use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeBuilder;
use wcf\system\l10n\L10nStorage;

/**
 * Imports reaction types.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 */
class ReactionTypeImporter extends AbstractImporter
{
    /**
     * @inheritDoc
     */
    protected $className = ReactionType::class;

    #[\Override]
    public function import(mixed $oldID, array $data, array $additionalData = [])
    {
        $file = new CreateFileFromExistingFile(
            $additionalData['fileLocation'],
            \basename($additionalData['fileLocation']),
            'com.woltlab.wcf.reactionType.icon',
            copy: true,
        )();
        if ($file === null) {
            return 0;
        }

        // Imported reaction types are owned by the administrator: they are not
        // linked to a language variable and their title is monolingual.
        $builder = ReactionTypeBuilder::forCreate()
            ->setTitle([L10nStorage::MONOLINGUAL => (string)$data['title']])
            ->setIconFileID($file->fileID);

        $handledColumns = ['reactionTypeID', 'title', 'iconFile', 'iconFileID', 'l10nIdentifier'];
        foreach ($data as $key => $value) {
            if (\in_array($key, $handledColumns, true)) {
                continue;
            }
            if ($value !== null && !\is_string($value) && !\is_int($value) && !\is_float($value)) {
                continue;
            }

            $builder->setCustomProperty($key, $value);
        }

        $reactionType = $builder->create();

        ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.reactionType', $oldID, $reactionType->reactionTypeID);

        return $reactionType->reactionTypeID;
    }
}
