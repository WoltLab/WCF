<?php

namespace wcf\system\template\plugin;

use wcf\system\exception\SystemException;
use wcf\system\template\TemplateEngine;

/**
 * See JsphrasePrefilterTemplatePlugin.
 *
 * This function exists to catch misuses of {jsphrase}
 * that violate the specs and have not been caught
 * by the prefilter.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Template\Plugin
 * @since 6.0
 */
final class JsphraseFunctionTemplatePlugin implements IFunctionTemplatePlugin
{
    /**
     * @inheritDoc
     */
    public function execute($tagArgs, TemplateEngine $tplObj)
    {
        $name = $tagArgs['name'] ?? '';
        if ($name === '') {
            throw new SystemException("missing 'name' argument in jsphrase tag");
        }

        if (!\preg_match('~[A-z0-9-_]+(\.[A-z0-9-_]+){2,}~', $name)) {
            throw new SystemException("The provided name does not appear to be a valid phrase identifier.");
        }

        throw new \LogicException("Unreachable");
    }
}
