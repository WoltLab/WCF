<?php

namespace wcf\data\file;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\file\processor\FileProcessor;

/**
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method File create()
 * @method FileEditor[] getObjects()
 * @method FileEditor getSingleObject()
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
            FileProcessor::getInstance()->delete($this->objects);
        }

        return parent::delete();
    }
}
