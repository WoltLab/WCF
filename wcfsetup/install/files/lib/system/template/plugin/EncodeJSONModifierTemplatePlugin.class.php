<?php

namespace wcf\system\template\plugin;

use wcf\system\template\TemplateEngine;
use wcf\util\StringUtil;

/**
 * @deprecated 5.5 - See StringUtil::encodeJSON()
 */
class EncodeJSONModifierTemplatePlugin implements IModifierTemplatePlugin
{
    /**
     * @inheritDoc
     */
    public function execute($tagArgs, TemplateEngine $tplObj)
    {
        return StringUtil::encodeJSON($tagArgs[0]);
    }
}
