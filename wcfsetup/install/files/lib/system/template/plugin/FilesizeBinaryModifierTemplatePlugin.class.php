<?php

namespace wcf\system\template\plugin;

use wcf\system\template\TemplateEngine;
use wcf\util\FileUtil;

/**
 * Template modifier plugin which formats a binary filesize (given in bytes).
 *
 * Usage:
 *  {$string|filesizeBinary}
 *  {123456789|filesizeBinary}
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class FilesizeBinaryModifierTemplatePlugin implements IModifierTemplatePlugin
{
    #[\Override]
    public function execute(array $tagArgs, TemplateEngine $tplObj)
    {
        return FileUtil::formatFilesizeBinary($tagArgs[0]);
    }
}
