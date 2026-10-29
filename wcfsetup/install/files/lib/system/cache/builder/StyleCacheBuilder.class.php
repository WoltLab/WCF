<?php

namespace wcf\system\cache\builder;

use wcf\system\cache\eager\StyleCache;
use wcf\system\style\StyleHandler;

/**
 * Caches the styles and style variables.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @deprecated 6.3 use `StyleHandler` instead
 */
class StyleCacheBuilder extends AbstractLegacyCacheBuilder
{
    #[\Override]
    public function rebuild(array $parameters): array
    {
        return [
            'default' => new StyleCache()->getCache()['default'],
            'styles' => StyleHandler::getInstance()->getStyles(),
        ];
    }

    #[\Override]
    public function reset(array $parameters = [])
    {
        new StyleCache()->rebuild();
    }
}
