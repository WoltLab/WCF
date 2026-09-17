<?php

namespace wcf\data\file;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\file\processor\FileProcessor;

/**
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use `FileBuilder` instead.
 *
 * @extends AbstractDatabaseObjectAction<File, FileEditor>
 */
class FileAction extends AbstractDatabaseObjectAction
{
    protected $className = FileEditor::class;

    #[\Override]
    public function delete()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        if ($this->objects !== []) {
            FileProcessor::getInstance()->delete(
                \array_map(
                    static fn(FileEditor $editor) => $editor->getDecoratedObject(),
                    $this->objects
                )
            );
        }

        return parent::delete();
    }
}
