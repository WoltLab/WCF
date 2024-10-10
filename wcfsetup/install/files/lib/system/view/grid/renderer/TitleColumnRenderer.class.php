<?php

namespace wcf\system\view\grid\renderer;

class TitleColumnRenderer extends DefaultColumnRenderer
{
    public function getClasses(): string
    {
        return 'columnTitle';
    }
}
