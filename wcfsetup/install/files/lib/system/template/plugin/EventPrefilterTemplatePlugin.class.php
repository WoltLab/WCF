<?php

namespace wcf\system\template\plugin;

use wcf\system\template\TemplateScriptingCompiler;
use wcf\system\WCF;

/**
 * Template profiler plugin which inserts template listener's code before compilation.
 *
 * Usage:
 *  {event name='foo'}
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class EventPrefilterTemplatePlugin implements IPrefilterTemplatePlugin
{
    /**
     * @inheritDoc
     */
    public function execute($templateName, $sourceContent, TemplateScriptingCompiler $compiler)
    {
        $ldq = \preg_quote($compiler->getLeftDelimiter(), '~');
        $rdq = \preg_quote($compiler->getRightDelimiter(), '~');

        return \preg_replace_callback(
            "~{$ldq}event\\ name\\=\\'([\\w]+)\\'{$rdq}~",
            static function ($match) use ($templateName) {
                return WCF::getTPL()->getTemplateListenerCode($templateName, $match[1]);
            },
            $sourceContent
        );
    }
}
