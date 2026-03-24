<?php

namespace wcf\data\unfurl\url;

use wcf\data\DatabaseObjectList;
use wcf\system\cache\runtime\FileRuntimeCache;

/**
 * Represents a list of unfurled urls.
 *
 * @author      Joshua Ruesweg
 * @copyright   2001-2021 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.4
 *
 * @extends DatabaseObjectList<UnfurlUrl>
 */
class UnfurlUrlList extends DatabaseObjectList
{
    public function __construct()
    {
        parent::__construct();

        if (!empty($this->sqlSelects)) {
            $this->sqlSelects .= ',';
        }
        $this->sqlSelects .= "unfurl_url_image.*";
        $this->sqlJoins .= "
            LEFT JOIN   wcf1_unfurl_url_image unfurl_url_image
            ON          unfurl_url_image.imageID = unfurl_url.imageID";
    }

    #[\Override]
    public function readObjects()
    {
        parent::readObjects();

        $fileIDs = [];
        foreach ($this->objects as $object) {
            if ($object->fileID !== null) {
                $fileIDs[] = $object->fileID;
            }
        }

        FileRuntimeCache::getInstance()->cacheObjectIDs($fileIDs);
    }
}
