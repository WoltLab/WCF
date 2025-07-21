<?php

namespace wcf\system\worker;

use wcf\data\file\FileEditor;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyEditor;
use wcf\data\trophy\TrophyList;
use wcf\system\trophy\command\MigrateLegacyCondition;

/**
 * Worker implementation for updating trophies.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @extends AbstractLinearRebuildDataWorker<TrophyList>
 */
final class TrophyRebuildDataWorker extends AbstractLinearRebuildDataWorker
{
    /**
     * @inheritDoc
     */
    protected $objectListClassName = TrophyList::class;

    /**
     * @inheritDoc
     */
    protected $limit = 100;

    #[\Override]
    public function execute()
    {
        parent::execute();

        foreach ($this->objectList as $trophy) {
            (new MigrateLegacyCondition($trophy))();

            $this->migrateFile($trophy);
        }
    }

    private function migrateFile(Trophy $trophy): void
    {
        // @phpstan-ignore property.notFound
        if ($trophy->type !== Trophy::TYPE_IMAGE || $trophy->imageFileID !== null || $trophy->iconFile === '') {
            return;
        }

        $file = FileEditor::createFromExistingFile(
            WCF_DIR . 'images/trophy/' . $trophy->iconFile,
            $trophy->iconFile,
            'com.woltlab.wcf.trophy'
        );

        if ($file === null) {
            return;
        }

        (new TrophyEditor($trophy))->update([
            'imageFileID' => $file->fileID,
            'iconFile' => '',
        ]);
    }
}
