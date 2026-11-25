<?php

namespace wcf\system\interaction;

use wcf\data\DatabaseObject;
use wcf\util\StringUtil;

/**
 * Represents an interaction that links to a controller which responds with a file download.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class DownloadLinkInteraction extends LinkInteraction
{
    #[\Override]
    public function render(DatabaseObject $object): string
    {
        return \sprintf(
            '<a href="%s" download>%s</a>',
            StringUtil::encodeHTML($this->getLink($object)),
            $this->getTitle($object)
        );
    }
}
