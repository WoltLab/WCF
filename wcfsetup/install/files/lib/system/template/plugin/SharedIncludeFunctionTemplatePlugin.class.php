<?php

namespace wcf\system\template\plugin;

use wcf\system\template\TemplateEngine;

/**
 * Usage:
 *   {sharedInclude file="test" application="wcf"}
 *   {sharedInclude file="test" application="wcf" var=$variable …}
 *
 * @author  Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class SharedIncludeFunctionTemplatePlugin implements IFunctionTemplatePlugin
{
    #[\Override]
    public function execute($tagArgs, TemplateEngine $tplObj)
    {
        if (!isset($tagArgs['file'])) {
            throw new \InvalidArgumentException("missing 'file' argument in sharedInclude tag");
        }
        $file = 'shared_' . $tagArgs['file'];
        $application = $tagArgs['application'] ?? 'wcf';
        $sandbox = $tagArgs['sandbox'] ?? false;

        unset($tagArgs['file'], $tagArgs['application'], $tagArgs['sandbox']);

        return WCF::getTPL()->fetch($file, $application, $tagArgs, $sandbox);
    }
}
