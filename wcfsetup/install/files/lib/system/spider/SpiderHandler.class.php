<?php

namespace wcf\system\spider;

use wcf\system\SingletonFactory;

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class SpiderHandler extends SingletonFactory
{
    public function getSpider(string $identifier): ?Spider
    {
        //TODO
        return null;
    }

    public function getIdentifier(string $userAgent): ?string
    {
        $userAgent = \strtolower($userAgent);
        //TODO

        return null;
    }
}
