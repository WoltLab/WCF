<?php

namespace wcf\system\view\grid\renderer;

use wcf\system\WCF;
use wcf\util\StringUtil;

class PhraseColumnRenderer extends DefaultColumnRenderer
{
    public function render(mixed $value, mixed $context = null): string
    {
        return StringUtil::encodeHTML(WCF::getLanguage()->get($value));
    }
}
