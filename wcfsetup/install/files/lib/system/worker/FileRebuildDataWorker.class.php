<?php

namespace wcf\system\worker;

use wcf\data\file\FileEditor;
use wcf\data\file\FileList;
use wcf\system\file\processor\exception\DamagedImage;
use wcf\system\file\processor\FileProcessor;

use function wcf\functions\exception\logThrowable;

/**
 * Worker implementation for updating files.
 *
 * @author Alexander Ebert
 * @copyright 2001-2014 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 *
 * @method FileList getObjectList()
 * @property-read FileList $objectList
 */
final class FileRebuildDataWorker extends AbstractLinearRebuildDataWorker
{
    /**
     * @inheritDoc
     */
    protected $objectListClassName = FileList::class;

    /**
     * @inheritDoc
     */
    protected $limit = 10;

    #[\Override]
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->sqlOrderBy = 'file.fileID';
    }

    #[\Override]
    public function execute()
    {
        parent::execute();

        $damagedFileIDs = [];
        foreach ($this->objectList as $file) {
            try {
                FileProcessor::getInstance()->generateWebpVariant($file);
                FileProcessor::getInstance()->generateThumbnails($file);
            } catch (DamagedImage $e) {
                logThrowable($e);

                $damagedFileIDs[] = $e->fileID;
            }
        }

        if ($damagedFileIDs !== []) {
            FileEditor::deleteAll($damagedFileIDs);
        }
    }
}
