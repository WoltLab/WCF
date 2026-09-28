<?php

namespace wcf\system\importer;

use wcf\command\file\CreateFileFromExistingFile;
use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeEditor;

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

        unset($data['iconFile']);
        $data['iconFileID'] = $file->fileID;

        /** @var ReactionType $reactionType */
        $reactionType = ReactionTypeEditor::create($data);

        ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.reactionType', $oldID, $reactionType->reactionTypeID);

        return $reactionType->reactionTypeID;
    }
}
